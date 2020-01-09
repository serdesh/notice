<?php

namespace app\controllers;

use app\components\AccessController;
use app\models\Contact;
use app\models\Functions;
use app\models\Petition;
use app\models\User;
use http\Exception;
use Yii;
use app\models\Users;
use app\models\UsersSearch;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends AccessController
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
//                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

//        if (User::isAdmin()) {
            //Отбираем только пользоватлей своей компании
            $dataProvider->query
                ->andWhere(['company_id' => User::getCompanyIdForUser()]);
//        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Users model.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $contact_model = Contact::findOne($model->contact_id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
        }

        if ($request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ФИО : ' . $model->fio,
                'size' => 'normal',
                'content' => $this->renderAjax('view', [
                    'model' => $this->findModel($id),
                    'contact_model' => $contact_model,
                ]),
                'footer' => Html::button('Отмена',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Изменить', ['update', 'id' => $id],
                        ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $this->findModel($id),
                'contact_model' => $contact_model,
            ]);
        }
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionManagerView($id)
    {
        $model = $this->findModel($id);
        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
        }

        return $this->render('manager-view', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreatorView($id)
    {
        $model = $this->findModel($id);
        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
        }

        return $this->render('creator-view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Users model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new Users();
        $contact_model = new Contact();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($contact_model->load($request->post()) && $contact_model->save()) {
                if ($model->load($request->post())) {
                    $model->contact_id = $contact_model->id;
                    if (!isset($model->company_id)) {
                        //Сохраняем ID компании к которой принадлежит текущий пользователь
                        $model->company_id = User::getCompanyIdForUser();

                        Yii::info($model->company_id, 'test');
                    }
                    if (!$model->save()) {
                        Yii::error($model->errors, '_error');
                        $contact_model->delete();

                        return [
                            'title' => "Создание пользователя",
                            'size' => 'large',
                            'content' => $this->renderAjax('create', [
                                'model' => $model,
                                'contact_model' => $contact_model,
                            ]),
                            'footer' => Html::button('Отмена',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                        ];
                    }
                }

                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => "Пользователи",
                    'size' => 'normal',
                    'content' => '<span class="text-success">' . Users::getRoleDescriptionByRoleName($model->permission) . ' создан успешно</span>',
                    'footer' => Html::button('Ок',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::a('Создать ещё', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                ];
            } else {
                return [
                    'title' => "Создать пользователя",
                    'size' => 'large',
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                        'contact_model' => $contact_model,
                    ]),
                    'footer' => Html::button('Отмена',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            }
        } else {
            if ($contact_model->load($request->post()) && $contact_model->save()) {

                if ($model->load($request->post())) {
                    $model->contact_id = $contact_model->id;
                    if (!$model->save()) {
                        $contact_model->delete();
                        Yii::error($model->errors, '_error');
                        Yii::$app->session->setFlash('error', 'Ошибка создания пользователя');
                        return $this->redirect(['index']);
                    }
                }
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'contact_model' => $contact_model,
                ]);
            }
        }

    }

    /**
     * Updates an existing Users model.
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
        $contact_model = Contact::findOne($model->contact_id) ?? null;

        if (!$contact_model) {
            $contact_model = new Contact();
        }

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
        }

        if ($request->isAjax) {

            Yii::info('is Ajax', 'test');

            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->load($request->post());
            $contact_model->load($request->post());

            if ($request->post() && $contact_model->save()) {
                $model->contact_id = $contact_model->id;
                if ($model->new_password) {
                    $model->password = $model->new_password;
                    $validation = true;
                } else {
                    $validation = false;
                }

                Yii::info($model->new_password, 'test');

                if (!$model->save($validation)) {
                    Yii::error($model->errors, '_error');
                    return [
                        'title' => "Изменить",
                        'size' => 'large',
                        'content' => $this->renderAjax('update', [
                            'model' => $model,
                            'contact_model' => $contact_model,
                        ]),
                        'footer' => Html::button('Отмена',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                    ];
                }
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'size' => 'large',
                    'title' => 'ФИО : ' . $model->fio,
                    'content' => $this->renderAjax('view', [
                        'model' => $model,
                        'contact_model' => $contact_model,
                    ]),
                    'footer' => Html::button('Отмена',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::a('Изменить', ['update', 'id' => $id],
                            ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                ];
            } else {
                return [
                    'title' => "Изменить",
                    'size' => 'large',
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                        'contact_model' => $contact_model,
                    ]),
                    'footer' => Html::button('Отмена',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error($model->errors, 'test');
                return $this->render('update', [
                    'model' => $model,
                    'contact_model' => $contact_model,
                ]);
            }
        }
    }

    /**
     * @param $id
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionChange($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($request->isGet) {
            return [
                'title' => "Смена пароля пользователя",
                'size' => 'normal',
                'content' => $this->renderAjax('change_password_form', [
                    'model' => $model,
                    'message' => 0,
                ]),
                'footer' => Html::button('Нет', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Да', ['class' => 'btn btn-primary', 'type' => "submit"])
            ];
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => "Смена пароля пользователя",
                    'content' => $this->renderAjax('change_password_form', [
                        'model' => $model,
                        'message' => 1,
                    ]),
                ];
            } else {
                return [
                    'title' => "Замена пароля пользователя",
                    'content' => $this->renderAjax('change', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Отмена',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        }
    }

    /**
     * Delete an existing Users model.
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
        $model = $this->findModel($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
        }

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */

            if ($id == Yii::$app->user->identity->getId()) {
                //Если пользователь пытается удалить сам себя
                Functions::setFlash('Вы не можете выполнить данное действие', 'warning');
                return $this->redirect(['index']);
            }

            if (Users::isAdmin() && Users::getPermissionForUser($id) == Users::USER_ROLE_ADMIN) {
                //Если админ пытается удалить админа
                Functions::setFlash('Вы не можете выполнить данное действие', 'warning');
                return $this->redirect(['index']);
            }

            //Проверяем пользователя на наличие созданных им заявок
            $petition_model = Petition::find()->andWhere(['created_by' => $model->id])->one() ?? null;

            if (isset($petition_model->id)) {
                Functions::setFlash('Пользователь имеет созданные заявки, удаление невозможно', 'warning');
                return $this->redirect(['index']);
            }

            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $model->delete();
            } catch (Exception $e) {
                Functions::setFlash('Ошибка удаления пользователя', 'error');
                return $this->redirect(['index']);
            }
            return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
        } else {
            /*
            *   Process for non-ajax request
            */
            $model->delete();

            return $this->redirect(['index']);
        }


    }

    /**
     * Delete multiple existing Users model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionBulkDelete()
    {
        $request = Yii::$app->request;
        $pks = explode(',', $request->post('pks')); // Array or selected records primary keys
        foreach ($pks as $pk) {

            if ($pk != 1) {
                $model = $this->findModel($pk);
                $model->delete();
            }
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
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемой страницы не существует.');
        }
    }

    /**
     * Получает специалистов компании
     * @return void
     */
    public function actionGetSpecByCompany()
    {
        $id = Yii::$app->request->post('company');
        echo "<option value='0'>Выберите исполнителя</option>";
        foreach (Users::find()->andWhere(['permission' => Users::USER_ROLE_SPECIALIST])->andWhere(['company_id' => $id])->each() as $specialist) {
            echo "<option value='" . $specialist->id . "'>" . $specialist->fio . "</option>";
        }
    }

    /**
     * Профиль для пользователя
     * @param int $id ID пользователя
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionProfile($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) throw new ForbiddenHttpException('Доступ запрещен');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->isGet) {
            return [
                'title' => "Профиль пользователя",
                'size' => 'normal',
                'content' => $this->renderAjax('profile', [
                    'model' => $model,
                ]),
                'footer' => Html::button('Закрыть', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
            ];
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => "Профиль сохранен",
                    'content' => $this->renderAjax('change_profile_form', [
                        'model' => $model,
                        'message' => 1,
                    ]),
                ];
            } else {
                return [
                    'title' => "Профиль",
                    'content' => $this->renderAjax('profile', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Отмена',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        }


    }
}
