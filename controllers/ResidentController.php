<?php

namespace app\controllers;

use app\models\Contact;
use app\models\Functions;
use app\models\History;
use app\models\Message;
use app\models\Petition;
use app\models\Phone;
use Yii;
use app\models\Resident;
use app\models\search\ResidentSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * ResidentController implements the CRUD actions for Resident model.
 */
class ResidentController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'bulkdelete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Resident models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ResidentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Resident model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $contact_model = Contact::findOne($model->contact_id) ?? null;

        if (!$contact_model) {
            $contact_model = new Contact();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Petition::find()->andWhere(['resident_id' => $model->id])
        ]);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->getFullName(),
                'size' => 'large',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'contact_model' => $contact_model,
                    'dataProvider' => $dataProvider,
                ]),
                'footer' => Html::button('Закрыть',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Редактировать', ['update', 'id' => $id],
                        ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'contact_model' => $contact_model,
            ]);
        }
    }

    /**
     * Creates a new Resident model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new Resident();
        $model->loadDefaultValues();

        $contact_model = new Contact();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Добавление жильца",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                        'contact_model' => $contact_model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($contact_model->save()) {
                    $model->load($request->post());
                    $model->contact_id = $contact_model->id;

                    if (!$model->save()) {
                        Yii::error($model->errors, '_error');
                        return $this->render('create', [
                            'model' => $model,
                            'contact_model' => $contact_model,
                        ]);
                    }

                    if ($model->resident_emails){
                        //Сохраняем мыла
                        $save_email = Functions::saveResidentEmails($model->id, $model->resident_emails);
                        if ($save_email !== true){
                            Yii::error($save_email, '_error');
                            $model->addError('resident_emails', $save_email);
                            return [
                                'title' => "Редактирование " . $model->getFullName(),
                                'content' => $this->renderAjax('update', [
                                    'model' => $model,
                                    'contact_model' => $contact_model,
                                ]),
                                'footer' => Html::button('Закрыть',
                                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                    Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                            ];
                        }
                    }

                    if ($model->phone){
                        //Сохраняем телефоны
                        Phone::setPhones($model->id, $model->phone);
                    }

                    $session = Yii::$app->session;
                    $request_page = $session->get('request_page');
                    $request_id = $session->get('request_id');
                    Yii::info('Страница: ' . $request_page, 'test');
                    Yii::info('ID: ' . $request_id, 'test');

                    if ($request_page && $request_id) {
                        $session->remove('request_page');
                        $session->remove('request_id');
                        //Добавление жильца происходит из другой формы, открываем форму из которой пришел запрос
                        Yii::info('Добавление из ' . $request_page, 'test');
                        switch ($request_page) {
                            case 'update_petition':
                                $petition_model = Petition::findOne($request_id) ?? null;
                                Yii::info('Обращение: ' . $petition_model->id, 'test');
                                if ($petition_model) {
                                    $petition_model->resident_id = $model->id;
                                    $petition_model->address = $model->getAddress();
                                    $petition_model->save();
                                    $msg_DataProvider = new ActiveDataProvider([
                                        'query' => Message::find()
                                            ->andWhere(['petition_id' => $petition_model->id]),
                                    ]);
                                    return [
                                        'title' => "Редактирование обращения №" . $petition_model->id,
                                        'content' => $this->renderAjax('/petition/update', [
                                            'model' => $petition_model,
                                            'msg_DataProvider' => $msg_DataProvider,
                                        ]),
                                        'footer' => Html::button('Закрыть',
                                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                                            . Html::button('Сохранить', [
                                                'class' => 'btn btn-primary',
                                                'type' => "submit"
                                            ])
                                    ];
                                }
                                break;
                        }
                    }
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Добавление жильца",
                        'content' => '<span class="text-success">Успешно добавлен</span>',
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Добавить ещё', ['create'],
                                ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                    ];
                } else {

                    return [
                        'title' => "Добавление жильца",
                        'content' => $this->renderAjax('create', [
                            'model' => $model,
                            'contact_model' => $contact_model,
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
            if ($contact_model->save()) {
                $model->load($request->post());
                $model->contact_id = $contact_model->id;


                if (!$model->save()) {
                    Yii::error($model->errors, '_error');
                    return $this->render('create', [
                        'model' => $model,
                        'contact_model' => $contact_model,
                    ]);
                }

                if ($model->phone){
                    //Сохраняем телефоны
                    Phone::setPhones($model->id, $model->phone);
                }

                if ($model->resident_emails){
                    //Сохраняем мыла
                    $save_email = Functions::saveResidentEmails($model->id, $model->resident_emails);
                    if ($save_email !== true){
                        Yii::error($save_email, '_error');
                        $model->addError('resident_emails', $save_email);
                        return $this->render('update', [
                            'model' => $model,
                            'contact_model' => $contact_model,
                        ]);
                    }
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
            return $this->render('create', [
                'model' => $model,
                'contact_model' => $contact_model,
            ]);
        }

    }

    /**
     * Updates an existing Resident model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $contact_model = Contact::findOne($model->contact_id);

        if (!isset($contact_model->id)) {
            $contact_model = new Contact();
            $contact_model->save();
        } else {
            $contact_model->phone = implode(', ', Contact::getPhonesWithContact($contact_model->id));
        }


        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Редактирование " . $model->getFullName(),
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                        'contact_model' => $contact_model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($contact_model->save()) {
                    $model->load($request->post());
                    $model->contact_id = $contact_model->id;

                    if (!$model->apartment_id) {
                        $model->apartment_id = Resident::findOne($model->id)->apartment_id;
                    }

                    if ($model->resident_emails){
                        $save_email = Functions::saveResidentEmails($model->id, $model->resident_emails);
                        if ($save_email !== true){
                            Yii::error($save_email, '_error');
                            $model->addError('resident_emails', $save_email);
                            return [
                                'title' => "Редактирование " . $model->getFullName(),
                                'content' => $this->renderAjax('update', [
                                    'model' => $model,
                                    'contact_model' => $contact_model,
                                ]),
                                'footer' => Html::button('Закрыть',
                                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                    Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                            ];
                        }
                    }
                    if ($model->phone){
                        //Сохраняем телефоны
                        Phone::setPhones($model->id, $model->phone);
                    }

                    if (!$model->save()) {
                        return [
                            'title' => "Редактирование " . $model->getFullName(),
                            'content' => $this->renderAjax('update', [
                                'model' => $model,
                                'contact_model' => $contact_model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                        ];
                    }

                    $url_to = $request->post('from');
                    if ($url_to) {
                        return $this->redirect($url_to, ['resident_id' => $model->id]);
                    }
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $model->getFullName(),
                        'content' => $this->renderAjax('view', [
                            'model' => $model,
                            'contact_model' => $contact_model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Редактировать', ['update', 'id' => $id],
                                ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    $contact_model->addError('contact_id', 'Контакты. Данные не получены');
                    Yii::error($contact_model->errors, '_error');
                    return [
                        'title' => "Редактирование " . $model->getFullName(),
                        'content' => $this->renderAjax('update', [
                            'model' => $model,
                            'contact_model' => $contact_model,
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
            if ($model->load($request->post()) && $model->save()) {
                if ($model->resident_emails){
                    //Сохраняем мыла
                    $save_email = Functions::saveResidentEmails($model->id, $model->resident_emails);
                    if ($model->phone){
                        //Сохраняем телефоны
                        Phone::setPhones($model->id, $model->phone);
                    }
                    if ($save_email !== true){
                        Yii::error($save_email, '_error');
                        $model->addError('resident_emails', $save_email);
                        return $this->render('update', [
                            'model' => $model,
                            'contact_model' => $contact_model,
                        ]);
                    }
                }
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                    'contact_model' => $contact_model,
                ]);
            }
        }
    }

    /**
     * Delete an existing Resident model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id) ?? null;
        $contact_model = Contact::findOne($model->contact_id) ?? null;
        //Проверяем наличие обращений жильца
        $petition = Petition::find()
                ->andWhere(['resident_id' => $id])
                ->one()->id ?? null;

        if ($petition) {
            //Если найдено хоть одно обращение
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'Удаление ' . $model->getFullName(),
                'content' => 'Ошибка удаления. У жильца имеются обращения. Для удаления жильца необходимо удалить все его обращения',
                'footer' => Html::button('Закрыть',
                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]),
            ];
        }

        $model->delete();
        if ($contact_model) {
            $contact_model->delete();
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
     * Delete multiple existing Resident model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
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
     * Finds the Resident model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Resident the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Resident::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
        }
    }

    /**
     * Получает полный адрес квартиры жильца
     * @var int $id ID жильца (resident)
     * @return null
     */
    public function actionGetAddress()
    {
        $id = Yii::$app->request->post('id');

        if ($id) {
            $resident_model = Resident::findOne($id) ?? null;

            if ($resident_model) {
                return $resident_model->getAddress();
            }
        }
        return null;
    }

    /**
     * Получает список жильцов, непривязанных к квартирам
     * @return string
     */
    public function actionUnrelated()
    {
        $searchModel = new ResidentSearch();
        $dataProvider = new ActiveDataProvider([
            'query' => Resident::find()
                ->andWhere(['IS', 'apartment_id', null])
                ->andWhere(['created_by_company' => Yii::$app->user->identity->company_id]),
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return array
     */
    public function actionShowAdditionalInfo($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Resident::findOne($id) ?? null;
        Yii::info($model->last_name, 'test');
        return [
            'title' => $model->getFullName(),
            'content' => $model->additional_info,
            'footer' => Html::button('Закрыть',
                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
        ];
    }

    /**
     * История по обращению (изменения статусовб тексты email'ов, создание задач)
     * @param int $id ID Обращения
     * @return array|string
     */
    public function actionHistoryPetition($id)
    {
        $petition_model = Petition::findOne($id) ?? null;

        $resident_model = Resident::findOne($petition_model->resident_id) ?? null;
        if (!$resident_model) {
            return [
                'title' => "404. Не найдено",
                'content' => 'Запрашиваемый заявитель не найден',
                'footer' => Html::button('Закрыть',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        }
        $msg_DataProvider = new ActiveDataProvider([
            'query' => Message::find()
                ->joinWith(['petition p'])
                ->andWhere(['p.resident_id' => $resident_model->id]),
        ]);
        $history_DataProvider = new ActiveDataProvider([
            'query' => History::find()
            ->andWhere(['petition_id' => $petition_model->id]),
        ]);

        if (Yii::$app->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "История по обращению " . $petition_model->id,
                'content' => $this->renderAjax('history', [
                    'msg_DataProvider' => $msg_DataProvider,
                    'history_DataProvider' => $history_DataProvider,
                ]),
                'footer' => Html::button('Закрыть',
                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        }

        return $this->render('history', [
            'msg_DataProvider' => $msg_DataProvider,
            'history_DataProvider' => $history_DataProvider,
        ]);
    }
}
