<?php

namespace app\controllers;

use app\components\AccessController;
use app\models\Apartment;
use app\models\Company;
use app\models\Functions;
use app\models\House;
use app\models\Resident;
use app\models\Room;
use app\models\UploadForm;
use app\models\Users;
use app\modules\fias\models\Dadata;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use yii\web\UploadedFile;

class SiteController extends AccessController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
//                'only' => ['logout'],
                'rules' => [
//                    [
//                        'actions' => ['index','logout'],
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
                    [
                        'actions' => ['login', 'error', 'backup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'import-ls', 'import-mkd'],
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['super_administrator', 'administrator', 'super_manager', 'manager', 'specialist'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $request = Yii::$app->request;
        $model = new LoginForm();

        if ($request->isGet && $request->isAjax) {
            Yii::info('Enter Superadmin', 'test');
            //Если входит супер админ под пользователем
            $company_id = $request->get('company');
            if ($company_id && Users::isSuperAdmin()) {
                $company_model = Company::findOne($company_id);
                $model->username = $company_model -> inn;
                $model->password = $company_model->password;
                $model->company_id = $company_model->id;
                $model->inn = $company_model->inn;
            } else {
                $model->addError('error', 'Ошибка аутентификации');
            }
        } else {
            Yii::info('Normal Enter', 'test');
            if (!$model->load(Yii::$app->request->post())) {
                $model->password = '';
                return $this->render('login', [
                    'model' => $model,
                ]);
            }
            Yii::info($model->toArray(), 'test');
        }
        $company = Company::findOne(['inn' => $model->inn]) ?? null;
        if (!$company){
            $model->addError('inn', 'Неизвестный ИНН');
            return $this->render('login', [
                'model' => $model,
            ]);
        }
        Yii::info($company->enabled, 'test');

        //Проверяем блокировку компании
        if (!$company->enabled){
            $model->addError('inn', 'Компания отключена. Обратитесь к суперадминистратору');
            return $this->render('login', [
                'model' => $model,
            ]);
        }

        if ($model->login()) {
            return $this->goBack();
        }

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect('login');
//        return $this->goHome();
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * @return string|Response
     */
    public function actionAuthorization()
    {
        if (isset(Yii::$app->user->identity->id)) {
            return $this->render('error');
        } else {
            Yii::$app->user->logout();
            return $this->redirect(['login']);
        }
    }

    /**
     * @throws \yii\base\Exception
     */
    public function actionBackup()
    {
        /** @var \demi\backup\Component $backup */
        $backup = Yii::$app->backup;

        $file = $backup->create();

        return 'Backup file created: ' . $file . PHP_EOL;
    }

    /**
     * @return \yii\console\Response|Response
     * @throws Exception
     */
    public function actionDownload()
    {
        $path = \Yii::getAlias('@app/runtime/logs');
        $file = $path . '/_error.log';

        if (file_exists($file)) {
            return \Yii::$app->response->sendFile($file);
        }
        throw new NotFoundHttpException('Файл не найден', 404);
    }

    /**
     * Импорт сведений о многоквартирных домах (МКД)
     * @return string|Response
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function actionImportMkd()
    {
        $model = new UploadForm();
        $request = Yii::$app->request;
        $errors = [];

        if ($request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');
            $tmp_name = time() + rand(999, 99999999);
            ini_set('memory_limit', '256M');
            set_time_limit(600);

            if ($model->upload($tmp_name)) {
                // Файл успешно загружен
                $inputFileName = 'uploads/' . $tmp_name . '.' . $model->file->extension;

                /** Load $inputFileName to a Spreadsheet Object  **/
                $spreadsheet = IOFactory::load($inputFileName);

                //Лист с кодами домов в системе ФИАС и адресом дома
                $character_sheet = $spreadsheet->getSheetByName('Характеристики МКД');

                //Лист с номерами квартир, связь с домом по полям "Адрес" и "Адрес МКД, в котором расположено жилое/нежилое помещение"
                $residential_sheet = $spreadsheet->getSheetByName('Жилые помещения');


                $non_residential_sheet = $spreadsheet->getSheetByName('Нежилые помещения');

                $room_sheet = $spreadsheet->getSheetByName('Комнаты');

                if (!$character_sheet) {
                    Functions::setFlash('Импорт невозможен. В импортируем файле отсутствует лист "Характеристики МКД"');
                } elseif (!$residential_sheet) {
                    Functions::setFlash('Импорт невозможен. В импортируем файле отсутствует лист "Жилые помещения"');
                } elseif (!$non_residential_sheet) {
                    Functions::setFlash('Импорт невозможен. В импортируем файле отсутствует лист "Нежилые помещения"');
                } elseif (!$room_sheet) {
                    Functions::setFlash('Импорт невозможен. В импортируем файле отсутствует лист "Комнаты"');
                }

                if (Yii::$app->session->get('error') != '') {
                    return $this->redirect('import-mkd');
                }

                $character_cells = $character_sheet->getCellCollection();
                $residential_cells = $residential_sheet->getCellCollection();
                $non_residential_cells = $non_residential_sheet->getCellCollection();
                $room_cells = $room_sheet->getCellCollection();

                //$columns_num = Coordinate::columnIndexFromString($cells->getHighestColumn());

//                Yii::info('Кол-во строк: ' . $character_cells->getHighestRow(), 'test');

                //Перебираем строки листа "Характеристики МКД", начиная с третьей
                for ($row = 3; $row <= $character_cells->getHighestRow(); $row++) {
                    //Получаем значение ячейки по адресу ($col,$row)
                    $address = trim($character_sheet->getCellByColumnAndRow(1, $row)->getValue()); //Адрес
                    $fias_number = trim($character_sheet->getCellByColumnAndRow(2,
                        $row)->getValue()); // Номер дома в ФИАС
                    $cadastral_number = trim($character_sheet->getCellByColumnAndRow(12,
                        $row)->getValue());//Кадастровый номер

                    $house_model = new House();
                    $house_model->import_address = $address;
                    $house_model->address = trim((new Dadata)->suggestionAddress($address));
                    $house_model->fias_number = $fias_number;
                    $house_model->cadastral_number = $cadastral_number;
                    $house_model->company_id = (new Users)->getCompanyIdForUser();

                    if (!House::isAvailable($house_model)) {
                        //Если дубликата дома не найдено - сохраняем
                        if (!$house_model->save()) {
                            Yii::error($house_model->errors, 'error');
                            array_push($errors, 'Ошибка сохранения. Адрес: ' . $house_model->address);
                            array_push($errors['Ошибка сохранения. Адрес: ' . $house_model->address],
                                $house_model->errors);
                        } else {
                            Yii::info('Сохранено. Адрес: ' . $house_model->address, 'test');
                        }
                    }
                }


                //Перебираем строки листа "Нежилые помещения", начиная с третьей
                for ($row = 3; $row <= $non_residential_cells->getHighestRow(); $row++) {
                    //Получаем значение ячейки по адресу ($col,$row)
                    $apartment_number = trim($non_residential_sheet->getCellByColumnAndRow(2,
                        $row)->getValue()); //Номер жилого помещения
                    $apartment_address = trim($non_residential_sheet->getCellByColumnAndRow(1,
                        $row)->getValue()); //Адрес

                    $apartment_model = new Apartment();

                    //По адресу ищем дом, к которому принадлежит помещение
                    foreach (House::find()->select('id, import_address')->each() as $house) {
                        if ($house->import_address == $apartment_address) {
                            $apartment_model->house_id = $house->id;
                            break;
                        }
                    }
                    Yii::info('Non residential apartment house_id: ' . $apartment_model->house_id, 'test');

                    $apartment_model->number = $apartment_number;
                    $apartment_model->is_residential = 0;
                    if (!Apartment::isAvailable($apartment_model)) {
                        if (!$apartment_model->save()) {
                            Yii::error('Ошибка сохранения квартиры №' . $apartment_number . '. Адрес: ' . $apartment_address,
                                '_error');
                            array_push($errors, 'Ошибка сохранения. Адрес: ' . $apartment_model->address);
                            array_push($errors['Ошибка сохранения. Адрес: ' . $apartment_model->address],
                                $apartment_model->errors);
                        } else {
                            Yii::info('Non residential apartment saved successfully', 'test');

                        }
                    }
                }


                //Перебираем строки листа "Жилые помещения", начиная с третьей

                $house_id = '';

                for ($row = 3; $row <= $residential_cells->getHighestRow(); $row++) {
                    //Получаем значение ячейки по адресу ($col,$row)
                    $apartment_number = trim($residential_sheet->getCellByColumnAndRow(2,
                        $row)->getValue()); //Номер жилого помещения
                    $apartment_address = trim($residential_sheet->getCellByColumnAndRow(1,
                        $row)->getValue()); //Адрес дома жилого помещения
                    $apartment_cadastral_number = trim($residential_sheet->getCellByColumnAndRow(7,
                        $row)->getValue()); //Адрес дома жилого помещения

                    //По адресу ищем дом, к которому принадлежит помещение
                    foreach (House::find()->select('id, import_address')->each() as $house) {
                        if ($house->import_address == $apartment_address) {
                            $house_id = $house->id;
                            break;
                        }
                    }

                    $apartment_model = new Apartment();
                    $apartment_model->house_id = $house_id;
                    $apartment_model->number = $apartment_number;
                    $apartment_model->is_residential = 1;
                    $apartment_model->cadastral_number = $apartment_cadastral_number;
                    if (!Apartment::isAvailable($apartment_model)) {
                        //Если дублей не найдено - сохраняем
                        if (!$apartment_model->save()) {
                            Yii::error('Ошибка сохранения квартиры №' . $apartment_number . '. Адрес: ' . $apartment_address,
                                '_error');
                            array_push($errors, 'Ошибка сохранения. Адрес: ' . $apartment_model->address);
                            array_push($errors['Ошибка сохранения. Адрес: ' . $apartment_model->address],
                                $apartment_model->errors);
                        } else {
                            Yii::info('Apartment saved successfully', 'test');
                        }
                    }

                }

                //Перибираем строки листа "Комнаты"
                for ($row = 3; $row <= $room_cells->getHighestRow(); $row++) {
                    $room_list_house_address = trim($room_sheet->getCellByColumnAndRow(1,
                        $row)->getValue()); //Адрес дома
                    $rooms_list_apartment_number = trim($room_sheet->getCellByColumnAndRow(2,
                        $row)->getValue()); //Номер жилого помещения
                    $room_number = trim($room_sheet->getCellByColumnAndRow(3,
                        $row)->getValue()); //Номер комнаты. В файле импорта комната записывается как "к3"
                    $room_number = str_replace('к', '', $room_number);

                    $apartment_id = Apartment::find()
                            ->joinWith(['house h'])
                            ->andWhere(['h.import_address' => $room_list_house_address])
                            ->andWhere(['apartment.number' => $rooms_list_apartment_number])
                            ->one()
                            ->id ?? null;

                    Yii::info('Apartment ID: ' . $apartment_id, 'test');

                    if (!$apartment_id) {
                        //Если помещения нет в базе
                        // (пздц, может быть и такое, что в файле импорта в "жилых" помещения нету,
                        // а в "комнатах" помещение уже есть. -_-`)
                        $apartment_model = new Apartment();
                        $apartment_model->house_id = House::find()->andWhere(['import_address' => $room_list_house_address])->one()->id ?? null;
                        $apartment_model->number = $rooms_list_apartment_number;
                        $apartment_model->is_residential = 1;
                        if (!Apartment::isAvailable($apartment_model)) {
                            if ($apartment_model->save()) {
                                Yii::info('Создано помещение на основании листа "Комнаты" и успешно сохранено', 'test');
                            } else {
                                Yii::error('Ошибка сохранения помещения №' . $apartment_model->number
                                    . '. ID Дома: ' . $apartment_model->house_id,
                                    '_error');
                            }
                            $apartment_id = $apartment_model->id;

                        }
                    }

                    $room_model = new Room();
                    $room_model->apartment_id = $apartment_id;
                    $room_model->number = $room_number;

                    if (!Room::isAvailable($room_model)) {
                        //Если дублей не найдено - сохраняем
                        if (!$room_model->save()) {
                            Yii::error('Ошибка сохранения комнаты №' . $room_number . '. Адрес: ' . $room_list_house_address,
                                '_error');
                        } else {
                            Yii::info('Комната №' . $room_model->number . ' успешно сохранена'
                                . 'Адрес: ' . $room_list_house_address . ' кв ' . $rooms_list_apartment_number, 'test');
                            Yii::info($room_model->toArray(), 'test');
                        }
                    }
                }

                unlink(Url::to('@webroot/uploads/' . $tmp_name . '.' . $model->file->extension));
            } else {
                //Ошибка загрузки файла
                Functions::setFlash('Ошибка загрузки файла на сервер');
                return $this->render('import-mkd', [
                    'model' => $model,
                ]);
            }
            if (count($errors)) {
                Yii::error('Все ошибки импорта', 'error');
                Yii::error($errors, 'error');
                $prepared_errors = '<pre>' . Json::encode($errors) . '</pre>';
                Yii::$app->session->setFlash('error', $prepared_errors);
            } else {
                Yii::$app->session->setFlash('success', 'Импорт проведен успешно');
                return $this->render('import_mkd', [
                    'model' => $model,
                ]);
            }
        }

        return $this->render('import_mkd', [
            'model' => $model,
        ]);
    }

    /**
     * Импорт сведений о лицевых счетах (ЛС)
     * @return string|Response
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function actionImportLs()
    {
        $model = new UploadForm();
        $errors = [];

        if (Yii::$app->request->isPost) {
            ini_set('memory_limit', '256M');
            set_time_limit(600);

            $model->file = UploadedFile::getInstance($model, 'file');
            $tmp_name = time() + rand(999, 99999999);

            if ($model->upload($tmp_name)) {
                // Файл успешно загружен
                $inputFileName = 'uploads/' . $tmp_name . '.' . $model->file->extension;

                /** Load $inputFileName to a Spreadsheet Object  **/
                $spreadsheet = IOFactory::load($inputFileName);

                //Лист с ФИО, СНИЛС
                $basic_sheet = $spreadsheet->getSheetByName('Основные сведения');

                //Лист с адресом помещения
                //"№ записи лицевого счета" связан с "№ записи" из листа "Основные сведения"
                $apartment_sheet = $spreadsheet->getSheetByName('Помещения');

                if (!$basic_sheet) {
                    Functions::setFlash('Импорт невозможен. В импортируем файле отсутствует лист "Основные сведения"');
                    unlink($inputFileName);

                    return $this->redirect('import-ls');
                } elseif (!$apartment_sheet) {
                    Functions::setFlash('Импорт невозможен. В импортируем файле отсутствует лист "Помещения"');
                    unlink($inputFileName);

                    return $this->redirect('import-ls');
                }

                $basic_cells = $basic_sheet->getCellCollection();
                $apartment_cells = $apartment_sheet->getCellCollection();

                //Перебираем записи с листа "Помещения"
                $company_id = Yii::$app->user->identity->company_id;
                for ($row = 3; $row <= $apartment_cells->getHighestRow(); $row++) {
                    $resident_model = new Resident();

                    $num_record_apartment = trim($apartment_sheet->getCellByColumnAndRow(1,
                        $row)->getValue()); //Номер записи
                    $house_address = trim($apartment_sheet->getCellByColumnAndRow(2, $row)->getValue()); //Адрес дома
                    $apartment_number = trim($apartment_sheet->getCellByColumnAndRow(5,
                        $row)->getValue());//Номер помещения
                    $room_number = trim($apartment_sheet->getCellByColumnAndRow(6, $row)->getValue()); //Номер комнаты
                    $room_number = str_replace('к', '', $room_number);

                    $ids = Apartment::getApartmentAndRoomId($house_address, $apartment_number, $room_number);

                    Yii::info($ids, 'test');

                    if (!$ids) {
                        continue;
                    }

                    $resident_model->apartment_id = $ids[0];
                    $resident_model->room_id = $ids[1];
                    $resident_model->num_record = $num_record_apartment;
                    $resident_model->owner = 1;
                    $resident_model->created_by_company = $company_id;

                    if (!$resident_model->save()) {
                        Yii::error($resident_model->errors, '_error');
                        array_push($errors, $resident_model->errors);
                    } else {
                        Yii::info('Информация добавлена.', 'test');
                    }
                }

                //Перебираем строки листа "Основные сведения"
                for ($row = 3; $row <= $basic_cells->getHighestRow(); $row++) {

                    $num_record = trim($basic_sheet->getCellByColumnAndRow(1, $row)->getValue()); //Номер записи

                    $resident_model = Resident::find()->andWhere(['num_record' => $num_record])->one() ?? null;

                    if (!$resident_model) {
                        continue;
                    }

                    $resident_model->last_name = trim($basic_sheet->getCellByColumnAndRow(7,
                        $row)->getValue()); //Фамилия
                    $resident_model->first_name = trim($basic_sheet->getCellByColumnAndRow(8, $row)->getValue()); //Имя
                    $resident_model->patronymic = trim($basic_sheet->getCellByColumnAndRow(9,
                        $row)->getValue()); //Отчество
                    $resident_model->snils = trim($basic_sheet->getCellByColumnAndRow(10, $row)->getValue()); //СНИЛС

                    //Т.к. попадаются записи Петров В.В. в поле фамилии проверяем и если так оно и есть - преобразовывваем
                    if (strpos($resident_model->last_name, '.') > 0
                        && !$resident_model->first_name
                        && !$resident_model->patronymic) {
                        Yii::info('Бракованая запись. Фамилия: ' . $resident_model->last_name, 'test');

                        $parts_fio = explode(' ', $resident_model->last_name); //Разделяем на фимилию и инициалы
                        $parts2_fio = explode('.', $parts_fio[1]); // Разделяем буквы имени и отчества
                        $resident_model->last_name = $parts_fio[0];
                        $resident_model->first_name = $parts2_fio[0];
                        $resident_model->patronymic = $parts2_fio[1];
                    }

                    if (!$resident_model->save()) {
                        Yii::error($resident_model->errors, '_error');
                        array_push($errors, $resident_model->errors);
                    } else {
                        Yii::info('Информация добавлена.', 'test');
                    }

                }

                if (count($errors)) {
                    Yii::error('Все ошибки импорта', 'error');
                    Yii::error($errors, 'error');
                    $prepared_errors = '<pre>' . Json::encode($errors) . '</pre>';
                    Yii::$app->session->setFlash('error', $prepared_errors);
                } else {
                    Functions::setFlash('Импорт проведен успешно', 'success');
                    unlink($inputFileName);
                }

            }
        }

        return $this->render('import_ls', [
            'model' => $model,
        ]);
    }

    public function actionTest()
    {
//        VarDumper::dump(Apartment::getApartmentAndRoomId('Уфа,Баязита Бикбая,19', '6'), 10, true);

//        $last_name = 'Петров В.А.';
//        $parts_fio = explode(' ', $last_name); //Разделяем на фимилию и инициалы
//        $parts2_fio = explode('.', $parts_fio[1]); // Разделяем буквы имени и отчества
//        VarDumper::dump( [
//            'last_name' => $parts_fio[0],
//            'first_name' => $parts2_fio[0],
//            'patronymic' => $parts2_fio[1],
//        ], 10, true);
    }

    public function actionMenuPosition()
    {
        $session = Yii::$app->session;

        if ($session->get('menu') == 'large') {
            $session->set('menu', 'small');
        } else {
            $session->set('menu', 'large');
        }
        Yii::info($session->get('menu'), 'test');
    }

    /**
     * Инструкции
     */
    public function actionInstructions()
    {
        return $this->render('instructions');
    }

}
