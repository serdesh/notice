<?php

namespace app\controllers;

use app\models\Functions;
use app\models\Settings;
use app\models\User;
use app\models\Users;
use app\modules\drive\models\Google;
use app\modules\drive\models\Yandex;
use Yii;
use app\models\Document;
use app\models\search\DocumentSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * DocumentController implements the CRUD actions for Document model.
 */
class DocumentController extends Controller
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
                    'delete' => ['post', 'get'],
                    'bulkdelete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Document models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (User::isAdmin()) {
            //Получаем документы домов,относящихся к компании текущего админа
            $dataProvider->query->andWhere(['h.company_id' => Users::getCompanyIdForUser()]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Document model.
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
            if ($model->house->company_id != Users::getCompanyIdForUser()) {
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
                    Html::a('Редактировать', ['Редактировать', 'id' => $id],
                        ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Document model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new Document();
        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Создание документа",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                if ($model->load($request->post())) {

                    $model->local_path = Yii::$app->session->get('houseFile');
                    Yii::$app->session->remove('houseFile');

                    if (!$model->save()) {

                        return [
                            'title' => "Создание документа",
                            'content' => $this->renderAjax('create', [
                                'model' => $model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                        ];
                    }
                    $inn = $model->house->company->inn;
                    $dir_type = '004';
                    $house_id = $model->house->id;
                    $file_name = basename($model->local_path);
                    $new_path = Yii::$app->name . '/' . $inn . '/' . $dir_type . '/' . $house_id;

                    if (!is_dir($new_path)) {
                        mkdir($new_path, 0777, true);
                    }

                    $result = rename($model->local_path, $new_path . '/' . $file_name);

                    if ($result) {
                        $temp_dir = str_replace($file_name, '', $model->local_path);
                        Functions::deleteDirectory($temp_dir);
                        $model->local_path = $new_path . '/' . $file_name;
                        if (!$model->save()) {
                            Yii::error($model->errors, '_error');
                        }
                    }
                    return $this->redirect('/document/index');
//                    return [
//                        'forceReload' => '#crud-datatable-pjax',
//                        'title' => "Создание документа",
//                        'content' => '<span class="text-success">Документ ' . $model->name . ' создан успешно</span>',
//                        'footer' => Html::button('Закрыть',
//                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
//                            Html::a('Создать ещё', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
//
//                    ];
                } else {
                    return [
                        'title' => "Создание документа",
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
            Yii::info('Non Ajax Request', 'test');
            if ($model->load($request->post())) {
                if (!$model->save()) {
                   Yii::error($model->errors, '_error');
                    return $this->redirect(['index']);
                }
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }

    }

    /**
     * Updates an existing Document model.
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
            if ($model->house->company_id != Users::getCompanyIdForUser()) {
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
                if ($model->load($request->post())) {

                    if (!$model->save()) {
                        Yii::error($model->errors, '_error');
                    }
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Редактирование " . $model->name,
                        'content' => $this->renderAjax('view', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Редактировать', ['Редактировать', 'id' => $id],
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
     * Delete an existing Document model.
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
            if ($model->house->company_id != Users::getCompanyIdForUser()) {
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }
        Yii::$app->session->set('houseFile', $model->local_path);
        $this->actionRemoveFile();
        $model->delete();
        Yii::$app->session->remove('houseFile');
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
     * Delete multiple existing Document model.
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
     * Finds the Document model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Document the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Document::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
        }
    }

    //Загрузка документа на сервер.

    /**
     * @throws \yii\base\Exception
     */
    public function actionUpload()
    {
        $session = Yii::$app->session;

        $temp_folder = Yii::$app->security->generateRandomString(15);

        $session->set('tmpFolder', $temp_folder);

        $uploadPath = 'uploads/' . $temp_folder . '/';

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        Yii::info($uploadPath, 'test');
        Yii::info($_FILES, 'test');
        $file = null;

        if (isset($_FILES['file'])) {

            $file = UploadedFile::getInstanceByName('file');

        } elseif (isset($_FILES['Document'])) {

            $request = Yii::$app->request;
            $model = new Document();
            $model->load($request->post());
            $file = UploadedFile::getInstance($model, 'file');
        }
        Yii::info($file, 'test');

        if ($file) {
            $newFileName = time() . '.' . $file->extension;
            // you can write save code here before uploading.
            $uploadPath = $uploadPath . $newFileName;
            if (!$file->saveAs($uploadPath)) {
                Yii::error('Ошибка сохранения документа', 'test');
            }
            $session->set('houseFile', $uploadPath);
        }


    }

    /**
     * Удаляет файл с сервера
     */
    public function actionRemoveFile()
    {
        try {
            $path_file = Yii::$app->session->get('houseFile');


            $path_part = explode('/', $path_file);
            $path_dir = $path_part[0] . '/' . $path_part[1];
            if (is_file($path_file)) {
                unlink($path_file);
            }
            if (is_dir($path_dir)) {
                rmdir($path_dir);
            }

            Yii::info('Removed file: ' . $path_file);
            Yii::info('Removed dir: ' . $path_dir);

            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), '_error');
        }
        return false;

    }

    /**
     * @return array
     * @throws \Google_Exception
     */
    public function actionSendToCloud()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $document_id = $request->post('document_id');
        if (!$document_id) {
            return ['fail', 'Отсутствует документ'];
        }

        $document_model = Document::findOne($document_id) ?? null;

        if (!$document_model) {
            return ['fail', 'Документ не найден'];
        }

        $drive = Settings::getValueByKeyFromCompany('drive_type', Users::getCompanyIdForUser());
        if (!$drive) {
            return ['fail', 'Не заданы настрйки онлайн диска, обратитесь к администратору'];
        }

        if (!is_file($document_model->local_path)) {
            return ['fail', 'Не найден файл для загрузки'];
        }

        $company_inn = $document_model->house->company->inn;
        $type_dir = '004'; //Папка для файлов, связанных с домом
        $house_id = $document_model->house->id;
        $file_name = basename($document_model->local_path);

        Yii::info('INN: ' . $company_inn, 'test');
        Yii::info('House ID: ' . $house_id, 'test');

        $cloud_path = $company_inn . '/' . $type_dir . '/' . $house_id . '/';
        Yii::info('Cloud path: ' . $cloud_path, 'test');

        if ($drive == 'yandex') {
            $result = Yandex::sendFile($document_model->local_path, $cloud_path . $file_name);
            if ($result) {
                $document_model->outer_id = $cloud_path . $file_name;
                Yii::info('outer_id: ' . $document_model->outer_id, 'test');
                $document_model->local_path = null;
            }
        } elseif ($drive == 'google') {
            $file = Google::sendFile($document_model->local_path, $cloud_path . $file_name);
            Yii::info($file, 'test');
            if (!isset($file['file'])) {
                return ['fail', 'Ошибка при загрузке файла на Google диск'];
            }
            if ($file['file']) {
                $document_model->outer_id = $file['file'];
                $document_model->local_path = null;
            }
        } else {
            return ['fail', 'Неизвестный тип онлайн диска'];
        }

        if (!$document_model->save()) {
            Yii::error($document_model->errors, '_error');
            //Если не удалось записать ID или путь к файлу удаляем загруженный документ
            if ($drive == 'yandex') {
                Yandex::deleteFile($document_model->outer_id);
            } elseif ($drive = 'google') {
                Google::deleteFile($document_model->outer_id);
            }
        }
        return ['success'];

    }

    /**
     * Отдает пользователю файл для загрузки
     * @param int $id ID документа
     * @return string
     * @throws \Google_Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionDownload($id)
    {
        $drive = Settings::getDrive();

        $document_model = Document::findOne($id) ?? null;

        if (!$document_model) return $this->render('/site/error', [
            'name' => 'Документ не найден.',
            'message' => 'Запрашиваемый документ не существует.'
        ]);

        $inn = $document_model->house->company->inn;
        $dir_type = '004';
        $house_id = $document_model->house->id;

        if ($drive == 'yandex') {
            $cloud_path_dir = $inn . '/' . $dir_type . '/' . $house_id;
            Yii::info('Cloud dir path: ' . $cloud_path_dir, 'test');

            $name = basename($document_model->outer_id);
            $file = $cloud_path_dir . '/' . $name;
            $downloaded_file = Yandex::downloadFile($file);
            if (file_exists($downloaded_file)) {
                Yii::info('Файл существует', 'test');
                return Yii::$app->response->sendFile($downloaded_file, $name);
            }
            Yii::info('Файл не существует', 'test');
        } elseif ($drive == 'google') {
            Google::downloadFile($document_model->outer_id);
        } else {
            //Если файл расположен на сервере
            $name = basename($document_model->local_path);
            $path = Yii::getAlias('@webroot');

            $file = $path . '/' . $document_model->local_path;

            Yii::info($file, 'test');

            if (file_exists($file)) {
                return Yii::$app->response->sendFile($file, $name);
            }
        }

        return $this->render('/site/error', [
            'name' => 'Файл не найден.',
            'message' => 'Ошибка при получении файла.'
        ]);

    }
}
