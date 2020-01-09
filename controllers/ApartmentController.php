<?php

namespace app\controllers;

use app\models\House;
use app\models\Petition;
use app\models\Room;
use app\models\Street;
use app\models\User;
use app\models\Users;
use Yii;
use app\models\Apartment;
use app\models\search\ApartmentSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * ApartmentController implements the CRUD actions for Apartment model.
 */
class ApartmentController extends Controller
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
     * Lists all Apartment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ApartmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (User::isAdmin()) {

            $houses = House::getHousesByCompany(User::getCompanyIdForUser());

            $dataProvider->query
                ->andWhere(['IN', 'apartment.house_id', $houses])
                ->select(['apartment.house_id', 'house.address'])
                ->distinct();
        }

        $sort = $dataProvider->getSort();
        $sort->defaultOrder = [
            'address' => SORT_ASC,
//            'number' => SORT_ASC,
        ];
        $dataProvider->setSort($sort);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Apartment model.
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
            if ($model->house->company_id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        Yii::info($model->toArray(), 'test');
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->getFullAddress($id),
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
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Apartment model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new Apartment();
        $model->loadDefaultValues();
        $room_models = new Room();
        $model->house_id = $request->get('house_id') ?? null;

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Добавление помещения",
                    'size' => 'large',
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                        'room_models' => $room_models,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {

                    $room_list = $request->post('Apartment')['room_list'] ?? null;

                    Yii::info($room_list, 'test');

                    if (isset($room_list)) {

                        $rooms = explode(',', $room_list);
                        Yii::info($rooms, 'test');
                        foreach ($rooms as $key => $number) {
                            $number = trim($number);

                            Yii::info('Number Room: ' . $number, 'test');

                            if ($number) {
                                if (!Room::isExist($number, $model->id)) {
                                    $room_model = new Room();
                                    $room_model->number = $number;
                                    $room_model->apartment_id = $model->id;
                                    $room_model->save();
                                }
                            }
                        }
                    }
                    $session = Yii::$app->session;
                    $request_page = $session->get('request_page');
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
                        'title' => "Добавление помещения",
                        'content' => '<span class="text-success">Квартира добавлена успешно</span>',
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Добавить ещё', ['create'],
                                ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                    ];
                } else {
                    return [
                        'title' => "Добавление помещения",
                        'content' => $this->renderAjax('create', [
                            'model' => $model,
                            'room_models' => $room_models,
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
                $room_models->load($request->post());
                if (isset($room_models->number)) {
                    $room_models->apartment_id = $model->id;
                    $room_models->save();
                }
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'room_model' => $room_models,
                ]);
            }
        }

    }

    /**
     * Updates an existing Apartment model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
            if ($model->house->company_id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        $room_models = Room::find()->andWhere(['apartment_id' => $id])->all() ?? null;
        if (!$room_models) {
            $room_models = new Room();
        }

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Редактирвание " . $model->address,
                    'size' => 'large',
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                        'room_models' => $room_models,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    $room_list = $request->post('Apartment')['room_list'] ?? null;
                    Yii::info($room_list, 'test');

                    /** @var array $new_room_list Новый список комнат */
                    $new_room_list = explode(',', $room_list);

                    Yii::info($new_room_list, 'test');

                    $db_room_list = Room::find()->andWhere(['apartment_id' => $model->id])->all() ?? null; //Список комнат из базы

                    $current_room_list = [];

                    //Приводим массив к формату массива с новыми значаниями
                    foreach ($db_room_list as $room) {
                        array_push($current_room_list, $room->number);
                    }
                    Yii::info($current_room_list, 'test');

                    /** @var array $new_rooms Добавляемые комнаты */
                    $new_rooms = array_diff($new_room_list, $current_room_list);
                    Yii::info($new_rooms, 'test');

                    /** @var array $removable Удаляемые комнаты */
                    $removable_rooms = array_diff($current_room_list, $new_room_list);
                    Yii::info($removable_rooms, 'test');

                    //Добавляем комнаты
                    foreach ($new_rooms as $key => $new_number) {
                        $room_model = new Room();
                        $room_model->apartment_id = $model->id;
                        $room_model->number = $new_number;
                        $result = $room_model->save();
                        Yii::info('Комната: ' . $room_model->number, 'test');
                        Yii::info('Успешно добавлено: ' . $result, 'test');
                    }

                    //Удаляем комнаты
                    foreach ($removable_rooms as $key => $number) {
                        $room_model = Room::find()
                            ->andWhere(['apartment_id' => $model->id])
                            ->andWhere(['number' => $number])->one();
                        Yii::info('Комната: ' . $room_model->number, 'test');
                        $result = $room_model->delete();
                        Yii::info('Успешно удалено: ' . $result, 'test');

                    }

                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Редактирвание " . $model->address,
                        'content' => $this->renderAjax('view', [
                            'model' => $model,
                            'room_models' => $room_models,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Редактировать', ['update', 'id' => $id],
                                ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    return [
                        'title' => "Редактирвание " . $model->address,
                        'content' => $this->renderAjax('update', [
                            'model' => $model,
                            'room_models' => $room_models,
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
                    'room_models' => $room_models,
                ]);
            }
        }
    }

    /**
     * Delete an existing Apartment model.
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
            if ($this->findModel($id)->house->company_id != Users::getCompanyIdForUser()) {
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
     * Delete multiple existing Apartment model.
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
     * Finds the Apartment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Apartment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Apartment::findOne($id)) != null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
        }
    }

    /**
     * @return Response|string
     */
    public function actionGetStreetListByType()
    {
        $type_id = Yii::$app->request->post('type');

        if ($type_id) {
            echo "<option value='0'>Выберите улицу</option>";
            foreach (Street::find()->where(['type_id' => $type_id])->each() as $model) {
                echo "<option value='" . $model->id . "'>" . $model->name . "</option>";
            }

        } else {
            Yii::$app->session->setFlash('error', 'Ошибка получения списка улиц');
            return $this->redirect('index');
        }

        return '';
    }

    /**
     * Получает все квартиры дома
     * @var int $house_id ID Дома
     * @return string|Response
     */
    public function actionGetApartments()
    {
        $house_id = Yii::$app->request->post('house');

        if ($house_id) {
            echo "<option value='0'>Выберите квартиру</option>";
            foreach (Apartment::find()->where(['house_id' => $house_id])->each() as $model) {
                echo "<option value='" . $model->id . "'>" . $model->number . "</option>";
            }
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка получения списка домов');
            return $this->redirect('index');
        }
        return '';
    }

    public function actionGetRooms()
    {
        $apartment_id = Yii::$app->request->post('apartment');

        if ($apartment_id) {
            echo "<option value='0'>Выберите комнату</option>";
            foreach (Room::find()->andWhere(['apartment_id' => $apartment_id])->each() as $model) {
                echo "<option value='" . $model->id . "'>" . $model->number . "</option>";
            }
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка получения списка комнат');
            return $this->redirect('index');
        }
        return '';
    }
}
