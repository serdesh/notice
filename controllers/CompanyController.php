<?php

namespace app\controllers;

use app\models\Contact;
use app\models\Users;
use Yii;
use app\models\Company;
use app\models\search\CompanySearch;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * CompanyController implements the CRUD actions for Company model.
 */
class CompanyController extends Controller
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
     * Lists all Company models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Company model.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $contact_model = Contact::findOne($model->contact_id);
        $request = Yii::$app->request;

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
        }

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "Компания " . $model->name,
                'content' => $this->renderAjax('view', [
                    'model' => $this->findModel($id),
                    'contact_model' => $contact_model,
                ]),
                'footer' => Html::button('Закрыть',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Редактировать', ['update', 'id' => $id],
                        ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'contact_model' => $contact_model,
            ]);
        }
    }

    /**
     * Creates a new Company model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws StaleObjectException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new Company();
        $contact_model = new Contact();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Создание новой компании",
                    'size' => 'large',
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                        'contact_model' => $contact_model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Создать', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                if ($contact_model->load($request->post()) && $contact_model->save()) {
                    if ($model->load($request->post())) {
                        $model->contact_id = $contact_model->id;
                        if (!$model->save()) {
                            Yii::error($model->errors, '_error');
                            $contact_model->delete();
                            return [
                                'title' => "Добавление компании",
                                'content' => $this->renderAjax('create', [
                                    'model' => $model,
                                    'contact_model' => $contact_model,
                                ]),
                                'footer' => Html::button('Закрыть',
                                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                    Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                            ];
                        }
                        return [
                            'forceReload' => '#crud-datatable-pjax',
                            'title' => "Добавление новой компании",
                            'content' => '<span class="text-success">Компания ' . $model->name . ' создана успешно</span>',
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::a('Добавить ещё', ['create'],
                                    ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                        ];
                    }

                } else {
                    return [
                        'title' => "Добавление компании",
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
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'contact_model' => $contact_model,
                ]);
            }
        }
        return $this->redirect(['index']);
    }

    /**
     * Updates an existing Company model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $contact_model = Contact::findOne($model->contact_id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
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
                        'contact_model' => $contact_model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($contact_model->load($request->post()) && $contact_model->save()) {
                    if ($model->load($request->post())) {
                        if ($model->new_password) {
                            $model->password = $model->new_password;
                            $validation = true;
                        } else {
                            $validation = false;
                        }

                        if (!$model->save($validation)) {
                            Yii::error($model->errors, 'error');
                            Yii::info($model->new_password, 'test');
                            Yii::info($model->password, 'test');
                            return [
                                'title' => "Редактирование " . $model->name,
                                'content' => $this->renderAjax('update', [
                                    'model' => $model,
                                    'contact_model' => $contact_model,
                                ]),
                                'footer' => Html::button('Закрыть',
                                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                    Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                            ];
                        }


                        return [
                            'forceReload' => '#crud-datatable-pjax',
                            'title' => "Компания " . $model->name,
                            'content' => $this->renderAjax('view', [
                                'model' => $model,
                                'contact_model' => $contact_model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::a('Редактировать', ['update', 'id' => $id],
                                    ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                        ];
                    }

                } else {
                    return [
                        'title' => "Редактирование " . $model->name,
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
     * Delete an existing Company model.
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
        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($this->findModel($id)->id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
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
     * Delete multiple existing Company model.
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
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
        }
    }

    /**
     * Меняет enabled компании на противоположный
     * @param int $id ID компании
     * @param string $notes Причина отключения
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionEnabled($id, $notes)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Company::findOne($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
        }

        Yii::info(!$model->enabled, 'test');
        $model->enabled = (int)!$model->enabled;
        $model->notes = $notes;
        if (!$model->save(false)) {
            Yii::error($model->errors, '_error');
            return ['error', 'Не возможно сохранить статус'];
        }
        return ['success', $model->enabled];
    }
}
