<?php

namespace app\controllers;

use app\models\Call;
use app\models\Company;
use app\models\Functions;
use app\models\Message;
use app\models\Phone;
use app\models\Resident;
use app\models\Status;
use app\models\User;
use app\models\Users;
use app\modules\fias\models\Dadata;
use Exception;
use PhpImap\Exceptions\InvalidParameterException;
use Yii;
use app\models\Petition;
use app\models\search\PetitionSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * PetitionController implements the CRUD actions for Petition model.
 */
class PetitionController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['check-mail'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'bulkdelete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Petition models.
     * @return mixed
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        Yii::$app->session->remove('request_page');

        if (User::isSuperAdmin() || Users::isSuperManager()) {
            $this->redirect('/company');
        }
//        if (User::isManager()) {
//            $this->redirect('/resident');
//        }

        $searchModel = new PetitionSearch();

        if ($request->isPost) {
            //Запрос с применением фильтра
            $dataProvider = new ActiveDataProvider([
                'query' => Petition::find()
                    ->joinWith('createdBy c'),
            ]);

            $query = $dataProvider->query;

            $company_id = $request->post('company');
            if ($company_id) {
                $query->andWhere(['c.company_id' => $company_id]);
            }

            $specialist_id = $request->post('specialist');
            if (Users::isSpecialist() || $specialist_id) {
                $query->andWhere(['specialist_id' => $specialist_id]);
            }

            $status_id = $request->post('status');
            if ($status_id) {
                $query->andWhere(['status_id' => $status_id]);
            }

            $archive_status_id = Status::findOne(['name' => 'Архивировано'])->id ?? null;
            if (!$request->post('search_in_archive')) {
                //Исключить поиск по архивировнанным
                if ($archive_status_id) {
                    $query->andWhere(['<>', 'status_id', $archive_status_id]);
                }
            }
            $expired_status_id = Status::findOne(['name' => 'Просрочено'])->id ?? null;
            if (!$request->post('search_in_expired')) {
                if ($expired_status_id) {
                    $query->andWhere(['<>', 'status_id', $expired_status_id]);
                }
            }

            $dataProvider->pagination = false;
        } else {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->andWhere(['<>', 'status_id', 5]); //без статуса "Архивировано"
            $dataProvider->setSort([
//                'attributes' => [
//                    'status_id',
//                    'manager_id',
//                    'specialist_id',
//                    'created_at'
//                ],
                'defaultOrder' => [
                    'status_id' => SORT_ASC,
                    'created_at' => SORT_DESC,
                    'specialist_id' => SORT_DESC,
                    'manager_id' => SORT_DESC,
                ]
            ]);
//            $dataProvider->query->orderBy('petition.status_id = 6', 'petition.status_id = 1');
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'type' => 'index',
        ]);
    }

    /**
     * Displays a single Petition model.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company->id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        $msg_DataProvider = new ActiveDataProvider([
            'query' => Message::find()
                ->andWhere(['petition_id' => $id]),
        ]);

        if ($request->isAjax) {

            Yii::info('is Ajax', 'test');

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "Обращение №" . $id,
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'msg_DataProvider' => $msg_DataProvider,
                ]),
                'footer' => Html::button('Закрыть',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Редактировать', ['update', 'id' => $id],
                        ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            Yii::info('is Non Ajax', 'test');
            return $this->render('view', [
                'model' => $model,
                'msg_DataProvider' => $msg_DataProvider,
            ]);
        }
    }

    /**
     * Creates a new Petition model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        Yii::info('actionCreate start', 'test');

        $request = Yii::$app->request;
        $model = new Petition();
        $model->loadDefaultValues();
        $model->call_id = $request->get('call_id') ?? null;

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::info('isAjax request', 'test');

            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                Yii::info('is Get request', 'test');

                return [
                    'title' => "Создание обращения",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    if ($model->call_id) {
                        //Добавляем к звонку обращение
                        $call = Call::findOne($model->call_id);
                        $call->petition_id = $model->id;
                        if (!$call->save()) {
                            Yii::error($call->errors, '_error');
                        }

                        //Пишем номер телефона заявителю в контакты
                        $result = Phone::addPhone($model->resident, $call->phone_number);

                        if ($result['error'] == 1){
                            Yii::error($result['data'], '_error');
                        } else {
                            Yii::info($result['data'], 'test');
                        }

                    }
                    Yii::$app->session->remove('request_page');

                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Создание обращения",
                        'content' => '<span class="text-success">Обращение №' . $model->id . ' создано успешно</span>',
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Создать ещё', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                    ];
                } else {
                    return [
                        'title' => "Создание обращения",
                        'content' => $this->renderAjax('create', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                    ];
                }
            }
        } else {
            /*
            *   Process for non-ajax request
            */
            Yii::info('non-ajax request', 'test');
            if ($model->load($request->post())) {

                if (!$model->save()) {
                    Functions::setFlash('Ошибка сохранения заявки', '_error');
                }
                if ($model->call_id) {
                    $call = Call::findOne($model->call_id);
                    $call->petition_id = $model->id;
                    if (!$call->save()) {
                        Yii::error($call->errors, '_error');
                    }

                    //Пишем номер телефона заявителю в контакты
                    $result = Phone::addPhone($model->resident, $call->phone_number);

                    Yii::info($result, 'test');
                    if ($result['error'] == 1){
                        Yii::error($result['data'], '_error');
                    } else {
                        Yii::info($result['data'], 'test');
                    }
                }
                return $this->redirect(['index']);
            } else {
                Yii::info('render Create form', 'test');
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }

    }

    /**
     * Updates an existing Petition model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company->id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }
        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                $model->additional_info = Resident::findOne($model->resident_id)->additional_info ?? null;
                $msg_DataProvider = new ActiveDataProvider([
                    'query' => Message::find()
                        ->andWhere(['petition_id' => $id]),
                ]);
                return [
                    'title' => "Редактирование обращения №" . $id,
                    'content' =>

                        $this->renderAjax('update', [
                            'model' => $model,
                            'msg_DataProvider' => $msg_DataProvider
                        ]),
                    'footer' => Html::submitButton('Сохранить', ['class' => 'btn btn-success pull-right'])
                ];
            } else {
                if ($model->load($request->post())) {
                    Yii::info($model->isAttributeChanged('status_id'), 'test');
                    //При статусе в работе специалист должен быть назначен
                    if ($model->status_id == Status::getStatusByName('В работе') && $model->specialist_id == null) {
                        return [
                            'title' => "Обращение №" . $id,
                            'content' => 'Для постановки обращения в работу необходимо выбрать специалиста',
                            'footer' => Html::a('Закрыть', ['update', 'id' => $id],
                                ['class' => 'btn btn-default pull-left', 'role' => 'modal-remote'])
                        ];
                    }
                    elseif ($model->status_id == Status::getStatusByName('В работе') && $model->specialist_id != null) { //&& $status_changed
                        //Если статус в работе + если указан спец /*+ если статус изменен, а не остался тот-же*/
                        //Проверяем не отсылалось ли уже сообщение о присвоении номера
                        $last_message_id = Message::find()
                            ->andWhere(['petition_id' => $model->id])
                            ->max('id');
                        $is_incoming = Message::findOne($last_message_id)->is_incoming ?? null;

                        Yii::info('Входящее сообщение: ' . $is_incoming, 'test');

                        if ($is_incoming || $model->email_id) {
                            //Если последнее сообщение - это входящее сообщение
                            //Отправляем заявителю письмо о присвоении номера обращения
                            $params = [
                                'subject' => 'Обращение[' . str_pad($model->id, 11, '0', STR_PAD_LEFT) . ']',
                                'text' => 'Уважаемый(ая) ' . $model->resident->getFullName() . '!' . ' Сообщаем вам, что ваше обращение зарегистрировано и передано в работу.' .
                                    ' Номер обращения ' . str_pad($model->id, 11, '0', STR_PAD_LEFT) .
                                    '. При переписке просьба не изменять тему письма.'
                            ];
                            if ($errors = $model->sendMail($params)) { //Если вернулись ошибки
                                Yii::error($errors, '_error');
                                return [
                                    'title' => "Редактирование обращения №" . $id,
                                    'content' => $this->renderAjax('update', [
                                        'model' => $model,
                                        'errors' => $errors,
                                    ]),
                                    'footer' => Html::button('Закрыть',
                                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                                        . Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                                ];
                            }
                        }

                    }

                    //Если статус решено и если меняет статус не специалист.
                    //При закрытии заявки специалистом не требуем написания ответа и не отправляем письма,
                    // т.к. это делает менеджер
                    if ($model->status_id == 3 && !Users::isSpecialist()) {
                        if (!trim($model->answer)) {
                            //Если ответ не написан
                            return [
                                'title' => "Обращение №" . $id,
                                'content' => 'Для закрытия обращения необходимо написать ответ обратившемуся',
                                'footer' => Html::a('Закрыть', ['update', 'id' => $id],
                                    ['class' => 'btn btn-default pull-left', 'role' => 'modal-remote'])
                            ];
                        } else {
                            //Получаем связанные обращения
                            $related_petitions = Petition::find()
                                ->orWhere(['relation_petition_id' => $model->id])
                                ->orWhere(['id' => $model->id])
                                ->orWhere(['id' => $model->relation_petition_id])
//                                ->asArray()
                                ->all();

                            //Отправляем ответ всем найденным
                            /** @var Petition $petition */
                            foreach ($related_petitions as $petition) {
                                Yii::info($petition->toArray(), 'test');
                                $params = [
                                    'subject' => 'Обращение[' . str_pad($petition->id, 11, '0',
                                            STR_PAD_LEFT) . '] Решено',
                                    'text' => $model->answer,
                                ];
                                $petition->sendMail($params);
                                $petition->answer = $model->answer; //Пишем ответ в каждое обращение
                                $petition->status_id = 3; //Статус Решено
                                if (!$petition->save()) {
                                    Yii::error($petition->errors, '_error');
                                }
                            }
                        }
                    }

                    Yii::info('Status ID: ' . $model->status_id, 'test');

                    //Если обращение создано на основе письма -
                    // проеряем записан ли email отправителя письма жильцу, привязанному к обращению

                    if ($model->where_type == 'email' && $model->resident_id && !$model->email_id){
                        /** @var int $email_id ID ResidentEmail модели*/
                        $email_id = Petition::checkEmailForPetition($model->id, $model->resident_id);
                        if (is_numeric($email_id)){
                            //Если пришел ID email адреса жильца
                            $model->email_id = $email_id;
                        }
                    }

                    if (!$model->save()) {
                        Yii::error($model->errors, '_error');
                        return [
                            'title' => "Редактирование обращения №" . $id,
                            'content' => $this->renderAjax('update', [
                                'model' => $model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                                . Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                        ];
                    }
                    Yii::$app->session->remove('request_page');
                    return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
                } else {
                    return [
                        'title' => "Редактирование обращения #" . $id,
                        'content' => $this->renderAjax('update', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                    ];
                }
            }
        } else {
            /*
            *   Process for non-ajax request
            */
            Yii::info('Non Ajax request', 'test');
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Delete an existing Petition model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company->id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        $model->delete();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }


    }

    /**
     * Delete multiple existing Petition model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     * @throws NotFoundHttpException
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionBulkdelete()
    {
        $request = Yii::$app->request;
        $pks = explode(',', $request->post('pks')); // Array or selected records primary keys
        foreach ($pks as $pk) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }

    }

    /**
     * Finds the Petition model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Petition the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Petition::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
        }
    }

    /**
     * Получает архивные обращения и выводит на странице обращений
     * @return string
     */
    public function actionArchive()
    {
        $searchModel = new PetitionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['status_id' => Status::getStatusByName('Архивировано')]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'type' => 'archive',
        ]);
    }

    /**
     * Меняет статус обращания на "В работе"
     * @return array|Response
     * @throws NotFoundHttpException
     */
    public function actionToWork()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $id = $request->post('id');
        $specialist_id = $request->post('specialist_id');
        $relation_petition_id = $request->post('relation_petition_id');
        $execution_date = $request->post('execution_date');

        $model = $this->findModel($id) ?? null;
        $model->status_id = Status::getStatusByName('В работе');
        $model->specialist_id = $specialist_id;
        $model->relation_petition_id = $relation_petition_id;
        $model->execution_date = Functions::getDateTimeForBase($execution_date);

        if (!$model->save()) {
            Yii::error($model->errors, '_error');
            return $model->errors;
        } else {
            return $this->redirect('/petition/index');
        }
    }

    /**
     * Приводит введенный адрес к нормальному виду
     * использует 'API подсказок' dadata.ru https://dadata.ru/api/suggest/
     * @param string $address Адрес, введенный пользователем
     * @return array Возвращает или ['success', clean_address] или ['error', 'text_error']
     */
    public function actionCheckAddressOnDadata($address)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
//
//        Yii::info('Address: ' . $address, 'test');
//
//        $httpClient = new \GuzzleHttp\Client([
//            'base_uri' => 'https://dadata.ru/api',
//        ]);
//        $client_dadata = new Client($httpClient, [
//            'token' => Settings::getValueByKey('dadata_token'),
//            'secret' => Settings::getValueByKey('dadata_secret'),
//
//        ]);
//        $clean_address = $client_dadata->cleanAddress($address);
//
//        Yii::info('Clean Address: ' . $clean_address, 'test');
//
//        return ['success', $clean_address->result];
        $result = (new Dadata())->suggestionAddress($address);

        if ($result) {
            return ['success', $result];
        }

        return ['error', 'Ошибка'];

    }

    /**
     * Все обращения со статусом "жалоба".
     * @return string
     */
    public function actionComplaint()
    {
        $searchModel = new PetitionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['petition_type' => Petition::PETITION_TYPE_COMPLAINT]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'type' => 'complaint',
        ]);
    }

    /**
     * Отчет
     * @return string
     */
    public function actionReport()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Petition::find()
                ->joinWith(['company c'])
                ->andWhere(['c.id' => User::getCompanyIdForUser()])
        ]);

        return $this->render('report', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Получает новые сообщения электронной почты для всех компаний и записывает обращения в базу
     * @return string
     */
    public function actionGetPetitionFromMail()
    {
        $errors = [];
        $result = [];
        foreach (Company::find()->andWhere(['enabled' => true])->each() as $company) {
            try {
                $result = (new Petition)->getMailFromCompany($company->id);
            } catch (InvalidParameterException $e) {
                Yii::error($e->getMessage(), '_error');
                array_push($errors, ['Company ' . $company->id => $e->getMessage()]);
            } catch (Exception $e) {
                array_push($errors, ['Company ' . $company->id => $e->getMessage()]);
                Yii::error($e->getMessage(), '_error');
            }

            if ($result) {
                array_push($errors, $result);
            }
        }

        if (count($errors) > 0) {
            return Json::encode($errors);
        }
        return Json::encode(['success' => 'true']);

    }

    /**
     * Получение почты для компании
     * @param $id
     */
    public function actionGetMailForCompany($id)
    {
        try {
            (new Petition)->getMailFromCompany($id);
        } catch (InvalidParameterException $e) {
            Yii::error($e->getMessage(), '_error');
//            Functions::setFlash($e->getMessage(),null, $e);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), '_error');
//            Functions::setFlash($e->getMessage(),null, $e);
        }

        $this->redirect('index');
    }

    /**
     * Проверяет почту всех компаний
     */
    public function actionCheckMail()
    {
        foreach (Company::find()->each() as $company) {
            try {
                (new Petition)->getMailFromCompany($company->id);
            } catch (InvalidParameterException $e) {
                Yii::error($e->getMessage(), '_error');
//            Functions::setFlash($e->getMessage(),null, $e);
            } catch (Exception $e) {
                Yii::error($e->getMessage(), '_error');
//            Functions::setFlash($e->getMessage(),null, $e);
            }
        }
        //Проверяем сроки обращений
        $this->actionCheckExecutionTime();
        return 'Ok';
    }

    /**
     * Присваивает обращению и нижестоящим связанным обращениям статус "Архивировано"
     * @param $id
     * @return Response
     */
    public function actionSendArchive($id)
    {
        $err = 0;
        //Подучаем все связанные обращения
        $models = Petition::find()
                ->andWhere(['id' => $id])
                ->orWhere(['relation_petition_id' => $id])->all() ?? null;

        if ($models) {
            foreach ($models as $model) {
                $model->status_id = 5; //Архивировано
                if (!$model->save()) {
                    $err += 1;
                    Yii::error($model->errors, '_error');
                }
            }
        }
        if ($err > 0) {
            Functions::setFlash('Имеются ошибки архивации обращения/ий.', 'error');
        }

        return $this->redirect('index');
    }

    /**
     * Проверяет сроки всех обращений "в работе"
     */
    public function actionCheckExecutionTime()
    {
        $petitions = Petition::find()
                ->andWhere(['status_id' => 2])
                ->all() ?? null;

        if ($petitions) {
            foreach ($petitions as $petition) {
                $exec_date = $petition->execution_date;
                $now = date('Y-m-d H:i:s', time());
                Yii::info('Дата исполнения: ' . $exec_date, 'test');
                Yii::info('Дата: ' . $now, 'test');
                Yii::info('Результат: ' . $exec_date < $now, 'test');
                if ($exec_date && $exec_date < $now) {
                    $petition->status_id = 6; //Просрочено
                    if (!$petition->save()) {
                        Yii::error($petition->errors, '_error');
                    }
                }
            }
            return 'Готово';
        } else {
            return 'Обращений в работе не найдено';
        }

    }

    /**
     * Удаляет сообщения относящиеся к обращению
     * @param int $id ID Обращения
     * @return Response
     */
    public function actionToSpam($id)
    {
        //TODO: Уточнить про удаление сообщений.
//        $messages = Message::find()
//            ->andWhere(['petition_id' => $id])
//            ->all() ?? null;
//        if ($messages){
//            foreach ($messages as $message){
//                if (!$message->delete()){
//                    Yii::error($message->errors, '_error');
//                }
//            }
//        }
        $model = Petition::findOne($id);
        $model->status_id = 3; //Решено
        //Добавляем ответ заявителю, чтобы система не ругалась
        $model->answer = 'Обращение помечено как спам. Ответ заявителю добавлен автоматически. Отправка ответа не осуществлялась.';
        if (!$model->save()) {
            Functions::setFlash($model->errors);
        }

        return $this->redirect('index');
    }

//    /**
//     * @return array
//     * @throws Exception
//     * @throws InvalidParameterException
//     */
//    public function actionGetTestMail()
//    {
//        $result = (new Petition)->getMailFromCompany(22);
//        return $result;
//    }
}
