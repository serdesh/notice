<?php

namespace app\modules\fias\controllers;

use app\modules\fias\models\Address;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\Controller;

/**
 * Default controller for the `address` module
 */
class AddressController extends Controller
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
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGetAreas()
    {
        $region_id = Yii::$app->request->post('id');

        $areas = Address::getAreasList($region_id);


        foreach ($areas as $key => $value) {
            echo "<option value='" . $key . "'>" . $value . "</option>";
        }
    }

    public function actionGetCities()
    {
        $area_id = Yii::$app->request->post('id');

        $cities = Address::getCitiesList($area_id);

        foreach ($cities as $key => $value) {
            echo "<option value='" . $key . "'>" . $value . "</option>";
        }
    }

    public function actionGetStreets()
    {
        $city_id = Yii::$app->request->post('id');

        $streets = Address::getStreetsList($city_id);

        foreach ($streets as $key => $value) {
            echo "<option value='" . $key . "'>" . $value . "</option>";
        }
    }

    public function actionGetHouses()
    {
        $street_id = Yii::$app->request->post('id');

        $houses = Address::getHousesList($street_id);

        foreach ($houses as $key => $value) {
            echo "<option value='" . $key . "'>" . $value . "</option>";
        }
    }
}
