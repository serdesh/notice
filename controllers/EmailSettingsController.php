<?php

namespace app\controllers;

use app\models\Users;
use Yii;
use app\models\EmailSettings;
use app\models\search\EmailSettingsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * EmailSettingsController implements the CRUD actions for EmailSettings model.
 */
class EmailSettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
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
     * Lists all EmailSettings models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EmailSettingsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single EmailSettings model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "EmailSettings #" . $id,
                'content' => $this->renderAjax('view', [
                    'model' => $this->findModel($id),
                ]),
                'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Edit', ['update', 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Creates a new EmailSettings model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new EmailSettings();

        $user_id = $request->get('id');
        $user_fio = '';

        if ($user_id) {
//            $user_fio = Users::getShortName($user_id);
            $user_fio = Users::findOne($user_id)->fio;
            $model->user_id = $user_id;
            $model->email = Users::findOne($model->user_id)->email ?? null;
        }
        Yii::info($model->toArray(), 'test');
        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Настройка почты для " . $user_fio,
                    'size' => 'large',
                    'content' => $this->renderAjax('/email/create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Проверить и сохранить',
                            ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($model->load($request->post())){
                    //Проверяем настройки почты перед сохранением
                    if ($errors = $model->checkSettings()){
                        //Если есть ошибки
                        $model->connect_errors = $errors;
                        Yii::error($model->connect_errors, 'error');
                        return [
                            'title' => "Настройка почты для " . $user_fio,
                            'size' => 'large',
                            'content' => $this->renderAjax('/email/update', [
                                'model' => $model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::button('Проверить и сохранить',
                                    ['class' => 'btn btn-primary', 'type' => "submit"])
                        ];
                    } else {
                        //Если нет ошибок выставляем флаг проеврки
                        $model->checked = 1;
                    }
                } else {
                    return [
                        'title' => "Редактирование настроек почты. " . $user_fio,
                        'size' => 'large',
                        'content' => $this->renderAjax('/email/update', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Проверить и сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                    ];
                }
                if ($model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Настройка почты для " . $user_fio,
                        'content' => '<span class="text-success">Настройки сохранены</span>',
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"])
                    ];
                } else {
                    return [
                        'title' => "Настройка почты для " . $user_fio,
                        'size' => 'large',
                        'content' => $this->renderAjax('/email/create', [
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
                return $this->redirect(['/email/view', 'id' => $model->id]);
            } else {
                return $this->render('/email/create', [
                    'model' => $model,
                ]);
            }
        }

    }

    /**
     * Updates an existing EmailSettings model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param int $user_id ID Пользователя
     * @return mixed
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    public function actionUpdate($user_id)
    {
        $request = Yii::$app->request;
        /** @var EmailSettings $model */
        $model = EmailSettings::find()->andWhere(['user_id' => $user_id])->one() ?? null;

        if (!$model) {
//             throw new NotFoundHttpException('Настройки не найдены');
            $model = new EmailSettings();
            $model->user_id = $user_id;
        }

        $user_model = Users::findOne($user_id) ?? null;
        $short_name = Users::getShortName($user_id);

        $model->email = $user_model->email ?? null;

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Редактирование настроек почты. " . $user_model->fio,
                    'size' => 'large',
                    'content' => $this->renderAjax('/email/update', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Проверить и сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($model->load($request->post())){
                    //Проверяем настройки почты перед сохранением
                    if ($errors = $model->checkSettings()){
                        //Если есть ошибки
                        $model->connect_errors = $errors;
                        Yii::error($model->connect_errors, 'error');
                        return [
                            'title' => "Настройка почты для " . $user_model->fio,
                            'size' => 'large',
                            'content' => $this->renderAjax('/email/update', [
                                'model' => $model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::button('Проверить и сохранить',
                                    ['class' => 'btn btn-primary', 'type' => "submit"])
                        ];
                    } else {
                        //Если нет ошибок выставляем флаг проеврки
                        $model->checked = 1;

                        if ($user_model->permission == Users::USER_ROLE_ADMIN) {
                            //Указываем, что почта используется по умолчанию, т.к. почта админа компании - является почтой админа
                            $model->as_default = 1;
                        }
                    }
                } else {
                    return [
                        'title' => "Редактирование настроек почты. " . $user_model->fio,
                        'size' => 'large',
                        'content' => $this->renderAjax('/email/update', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Проверить и сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                    ];
                }
                if ($model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Настройки почты. " . $user_model->fio,
                        'size' => 'large',
                        'content' => $this->renderAjax('/email/view', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Редактировать', ['update', 'user_id' => $model->user_id],
                                ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    Yii::error($model->errors, 'error');
                    return [
                        'title' => "Редактирование настроек почты. " . $user_model->fio,
                        'size' => 'large',
                        'content' => $this->renderAjax('/email/update', [
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
                return $this->redirect(['/email/view', 'id' => $model->id]);
            } else {
                return $this->render('/email/update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Delete an existing EmailSettings model.
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
     * Delete multiple existing EmailSettings model.
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
     * Finds the EmailSettings model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return EmailSettings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EmailSettings::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
