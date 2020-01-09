<?php

namespace app\controllers;

use app\models\Company;
use app\models\Document;
use app\models\Petition;
use app\models\Settings;
use app\models\User;
use app\models\Users;
use app\modules\drive\models\Google;
use app\modules\drive\models\Yandex;
use Exception;
use Yii;
use app\models\House;
use app\models\search\HouseSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * HouseController implements the CRUD actions for House model.
 */
class HouseController extends Controller
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
     * Lists all House models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HouseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (User::isAdmin()) {
            $dataProvider->query
                ->andWhere(['company_id' => User::getCompanyIdForUser()]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single House model.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;

        $model = $this->findModel($id);
        $doc_model = $model->document;

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->address,
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'doc_model' => $doc_model,
                ]),
                'footer' => Html::button('Закрыть',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Редактировать', ['update', 'id' => $id],
                        ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'doc_model' => $doc_model,
            ]);
        }
    }

    /**
     * Creates a new House model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new House();
        $doc_model = new Document();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Создание дома",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                        'doc_model' => $doc_model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                if ($doc_model->load($request->post())) {
                    $doc_model->local_path = Yii::$app->session->get('houseFile');
                    Yii::$app->session->remove('houseFile');
                    $doc_model->created_by = Yii::$app->user->id;
                    $doc_model->save();
                    if ($model->load($request->post())) {
                        $model->document_id = $doc_model->id;
                        if (!$model->save()) {
                            return [
                                'title' => "Создание дома",
                                'content' => $this->renderAjax('create', [
                                    'model' => $model,
                                    'doc_model' => $doc_model,
                                ]),
                                'footer' => Html::button('Закрыть',
                                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                    Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                            ];
                        }
                    } else {
                        $doc_model->delete();
                        Yii::error('Не удалось загрузить модель дома', '_error');
                        Yii::$app->session->setFlash('error', 'Не удалось загрузить модель дома');
                        $this->redirect('index');
                    }
                    $request_page = Yii::$app->session->get('request_page');

                    Yii::info('Страница: ' . $request_page, 'test');

                    if ($request_page) {
                        Yii::$app->session->remove('request_page');
                        //Добавление жильца происходит из другой формы, открываем форму из которой пришел запрос
                        Yii::info('Добавление из ' . $request_page, 'test');
                        $petition_model = new Petition();
                        return [
                            'title' => "Создание обращения",
                            'content' => $this->renderAjax('/petition/create', [
                                'model' => $petition_model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                                . Html::button('Сохранить', [
                                    'class' => 'btn btn-primary',
                                    'type' => "submit"
                                ])
                        ];
                    }
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Создание дома",
                        'content' => '<span class="text-success">Дом создан успешно</span>',
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Создать ещё', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                    ];
                } else {
                    return [
                        'title' => "Создание дома",
                        'content' => $this->renderAjax('create', [
                            'model' => $model,
                            'doc_model' => $doc_model,
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
                    'doc_model' => $doc_model,
                ]);
            }
        }

    }

    /**
     * Updates an existing House model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Google_Exception
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $doc_model = $model->document;

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->company_id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        if (!$doc_model) {
            $doc_model = new Document();
        }

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $model->address,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                        'doc_model' => $doc_model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($doc_model->load($request->post())) {
                    //Перемещаем файл
                    $drive = Settings::getValueByKeyFromCompany('drive_type', Users::getCompanyIdForUser());
                    $inn = Company::findOne(Users::getCompanyIdForUser())->inn ?? '000000';
                    $path_file = Yii::$app->session->get('houseFile'); //Путь к сохраненному файлу
                    $file_name = basename($path_file);
                    $root_directory = Yii::$app->name;

                    if (!is_dir($root_directory)) {
                        mkdir($root_directory, 0777);
                    };
                    $destination_path_directory = $inn . '/004/' . $model->id;

                    if (!$drive) {
                        //Если нет настройки онлайн диска - сохраняем локально
                        $path_dir = $root_directory . '/' . $destination_path_directory;
                        if (!is_file($path_dir)) {
                            mkdir($path_dir, 0777, true);
                        }
                        $new_path_file = $path_dir . '/' . $file_name;
                        rename($path_file, $new_path_file);
                        $doc_model->local_path = $new_path_file;
                    } elseif ($drive == 'yandex') {
                        //Сохраняем на яндекс диске
                        $result = Yandex::sendFile($path_file, $destination_path_directory . '/' . $file_name);
                        if (!$result) {
                            Yii::error('Ошибка загрузки файла на яндекс диск');
                        } else {
                            $doc_model->outer_id = $root_directory . '/' . $destination_path_directory . '/' . $file_name;
                            $doc_model->local_path = null;
                        }
                    } elseif ($drive == 'google') {
                        //Сохранем в Google Drive
                        $result = Google::sendFile($path_file, $destination_path_directory . '/' . $file_name);
                        if (isset($result['file'])) {
                            $doc_model->outer_id = $result['file'];
                        }
                        $doc_model->local_path = null;
                    }
//                    $doc_model->local_path = Yii::$app->session->get('houseFile');
                    Yii::$app->session->remove('houseFile');
                    $doc_model->save();
                    $model->load($request->post());
                    $model->document_id = $doc_model->id;
                    if (!$model->save()) {
                        return [
                            'title' => $model->address,
                            'content' => $this->renderAjax('update', [
                                'model' => $model,
                                'doc_model' => $doc_model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                        ];
                    }
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $model->address,
                        'content' => $this->renderAjax('view', [
                            'model' => $model,
                            'doc_model' => $doc_model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Редактировать', ['update', 'id' => $id],
                                ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    return [
                        'title' => $model->address,
                        'content' => $this->renderAjax('update', [
                            'model' => $model,
                            'doc_model' => $doc_model,
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
                    'doc_model' => $doc_model,
                ]);
            }
        }
    }

    /**
     * Delete an existing House model.
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
            if ($model->company_id != Users::getCompanyIdForUser()) {
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
     * Delete multiple existing House model.
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
     * Finds the House model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return House the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = House::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
        }
    }

    /**
     * Формирует список домов улицы
     * @return bool|Response|string
     */
    public function actionGetHouses()
    {
        $street_id = Yii::$app->request->post('street');

        if ($street_id) {
            echo "<option value='0'>Выберите дом</option>";
            foreach (House::find()->where(['street_id' => $street_id])->each() as $model) {
                echo "<option value='" . $model->id . "'>" . $model->number . "</option>";
            }
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка получения списка улиц');
            return $this->redirect('index');
        }

        return true;
    }

    /**
     * Отдает пользователю файл для загрузки
     * @param string $file Путь к файлу
     * @param string $name Имя файла
     * @return string
     */
    public function actionDownload($file, $name)
    {
        $path = Yii::getAlias('@webroot');
        $file = $path . '/' . $file;

        Yii::info($file, 'test');

        if (file_exists($file)) {
            return Yii::$app->response->sendFile($file, $name);
        }

        return $this->render('/site/error', [
            'name' => 'Файл не найден.',
            'message' => 'Запрашиваемый файл не существует.'
        ]);

    }
}
