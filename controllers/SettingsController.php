<?php

namespace app\controllers;

use app\models\Users;
use app\modules\drive\models\Auth;
use Yii;
use app\models\Settings;
use app\models\search\SettingsSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * SettingsController implements the CRUD actions for Settings model.
 */
class SettingsController extends Controller
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
     * Lists all Settings models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SettingsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andWhere(['company_id' => Users::getCompanyIdForUser()])
            ->orWhere(['user_id' => Yii::$app->user->id]);

        //Получаем настройку онлайн дисков
//        $drive = Settings::getValueByKeyFromCompany('drive_type', Users::getCompanyIdForUser());

        $all_settings = ArrayHelper::map($dataProvider->query->all(), 'key', 'value');

        Yii::info($all_settings, 'test');

        return $this->render('index', [
//            'searchModel' => $searchModel,
//            'dataProvider' => $dataProvider,
//            'drive' => $drive,
            'all_settings' => $all_settings,
        ]);
    }

    /**
     * Displays a single Settings model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->name,
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
                'footer' => Html::button('Закрыть',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Редактировать', ['update', 'id' => $id],
                        ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Creates a new Settings model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new Settings();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Создание настройки",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Создать', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Создание настройки",
                        'content' => '<span class="text-success">Настройка создана успешно</span>',
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Создать ещё', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                    ];
                } else {
                    return [
                        'title' => "Создание настройки",
                        'content' => $this->renderAjax('create', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Создать', ['class' => 'btn btn-primary', 'type' => "submit"])

                    ];
                }
            }
        } else {
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }

    }

    /**
     * Updates an existing Settings model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Редактирование " . $model->name,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $model->name,
                        'content' => $this->renderAjax('view', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Редактировать', ['update', 'id' => $id],
                                ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    return [
                        'title' => "Редактирование " . $model->name,
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
     * Delete an existing Settings model.
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

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($this->findModel($id)->company_id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        $this->findModel($id)->delete();

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
     * Delete multiple existing Settings model.
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
     * Finds the Settings model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Settings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Settings::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
        }
    }

    /**
     * Сохраняет выбранное облако
     * @param string $drive Наименование облака (yandex, google)
     * @return string
     */
    public function actionSaveDrive($drive)
    {
        if ($drive != 'yandex' && $drive != 'google' && $drive != 'nothing') {
            return 'Неизвестные данные';
        }
        $company_id = Users::getCompanyIdForUser();

        $settings = Settings::find()
                ->andWhere(['company_id' => $company_id])
                ->andWhere(['key' => 'drive_type'])
                ->one() ?? null;

        if (!$settings) {
            $settings = new Settings([
                'key' => 'drive_type',
                'name' => 'Тип используемого файлового облака',
                'company_id' => $company_id,
            ]);
        }

        if ($drive == 'nothing') {
            $settings->value = null;
        } else {
            $settings->value = $drive;
        }

        if (!$settings->save()) {
            Yii::error($settings->errors, '_error');
            return 'Ошибка сохранения статуса';
        }

        if ($settings->value){
            return 'Диск по умолчнию успешно изменен';
        } else {
            return 'Использование онлайн хранилищ отключено';
        }

    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function actionCredentialsUpload()
    {

        Yii::info('MIME type: ' . mime_content_type($_FILES['credentials']['tmp_name']), 'test');
        if (mime_content_type($_FILES['credentials']['tmp_name']) != 'text/plain') {
            Yii::error('Файл не является text/plain файлом', '_error');
            throw new NotAcceptableHttpException('Некорректный файл');
        }
        $result = copy($_FILES['credentials']['tmp_name'], Url::to('@app/tokens/google/credentials.json'));

        if ($result) {
            return $this->redirect('index');
        }
        throw new HttpException(500, 'Ошибка сохранения файла');

    }

    /**
     * Отключает онлайн диск
     * @param string $drive yandex/google
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionResetDrive($drive)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($drive == Settings::DRIVE_YANDEX) {
            //Удаляем токен
            $settings = Settings::find()
                    ->andWhere(['key' => 'yandex_disk_token'])
                    ->andWhere(['company_id' => Users::getCompanyIdForUser()])
                    ->one() ?? null;

            if ($settings) {
                $settings->delete();
            }
            return ['success', Url::to(['/settings'], true)];
        } elseif ($drive == Settings::DRIVE_GOOGLE) {
            $settings = Settings::find()
                    ->andWhere(['key' => 'google_refresh_token'])
                    ->andWhere(['company_id' => Users::getCompanyIdForUser()])
                    ->one() ?? null;
            if ($settings) {
                $settings->delete();
            }

            $settings = Settings::find()
                    ->andWhere(['key' => 'google_root_folder_id'])
                    ->andWhere(['company_id' => Users::getCompanyIdForUser()])
                    ->one() ?? null;
            if ($settings) {
                $settings->delete();
            }

            //Удаляем файл токена
            $token_file = Url::to(Auth::GOOGLE_TOKEN_DIR . 'token_' . Users::getCompanyIdForUser() . '.json');
            Yii::info('Путь к файлу токена: ' . $token_file, 'test');
            if (file_exists($token_file)) {
                unlink($token_file);
            }
            return ['success', Url::to(['/settings'], true), 'https://myaccount.google.com/u/0/permissions'];
        }
        return ['fail', 'Неизвестный тип диска'];
    }

    public function actionSaveAtc()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $company_id = Users::getCompanyIdForUser();
        $code = $request->post('code');
        $key = $request->post('key');

        if ($company_id && ($code || $key)) {
            if (!Settings::keyExist(Settings::KEY_ATC_CODE)) {
                //Если настройки нет - создем
                $model = new Settings([
                    'key' => Settings::KEY_ATC_CODE,
                    'name' => 'Уникальный код вашей АТС',
                    'company_id' => $company_id,
                ]);
            } else {
                $model = Settings::find()
                    ->andWhere(['key' => Settings::KEY_ATC_CODE])
                    ->andWhere(['company_id' => Users::getCompanyIdForUser()])
                    ->one() ?? null;
            }

            if ($model){
                Yii::info($model->toArray(), 'test');
                $model->value = $code;

                if (!$model->save()) {
                    Yii::error($model->errors, '_error');
                    return ['fail', 'Ошибка сохранения кода АТС'];
                }
            }


            if (!Settings::keyExist(Settings::KEY_ATC_KEY)) {
                //Если настройки нет - создем
                $model = new Settings([
                    'key' => Settings::KEY_ATC_KEY,
                    'name' => 'Ключ для создания подписи',
                    'company_id' => $company_id,
                ]);
            } else {
                $model = Settings::find()
                    ->andWhere(['company_id' => Users::getCompanyIdForUser()])
                    ->andWhere(['key' => Settings::KEY_ATC_KEY])
                    ->one() ?? null;
            }

            if ($model){
                Yii::info($model->toArray(), 'test');
                $model->value = $key;

                if (!$model->save()) {
                    Yii::error($model->errors, '_error');
                    return ['fail', 'Ошибка сохранения ключа для создания подписи'];
                }
            }

            return ['success', 'Операция проведена успешно'];
        }
        return ['fail', 'Отсутствует код АТС и ключ для создания подписи'];
    }

    public function actionGetPhoneSettings()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $company_id = Users::getCompanyIdForUser();

        if (!$company_id) return ['fail', 'Ошибка доступа'];

        $key = Settings::getValueByKeyFromCompany('atc_code', $company_id);
        if (!$key) return ['fail', 'Отсутствует уникальный код АТС'];

        $certificate = Settings::getValueByKeyFromCompany('atc_key', $company_id);
        if (!$certificate) return ['fail', 'Отсутствует ключ для создания подписи'];

        return ['success', $key, $certificate];
    }
}
