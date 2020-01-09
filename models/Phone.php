<?php

namespace app\models;

use app\models\query\PhoneQuery;
use Yii;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "phone".
 *
 * @property int $id
 * @property string $number Номер телефона (с кодом страны)
 * @property int $contact_id Владелец телефонного номера
 *
 * @property Contact $contact
 */
class Phone extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'phone';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contact_id'], 'integer'],
            [['number'], 'string', 'max' => 255],
            [['contact_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contact::className(), 'targetAttribute' => ['contact_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'number' => 'Номер телефона (с кодом страны)',
            'contact_id' => 'Владелец телефонного номера',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    /**
     * {@inheritdoc}
     * @return PhoneQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PhoneQuery(get_called_class());
    }

    /**
     * Добавляет, если необходимо, телефоны жильцу
     * @param $resident_id
     * @param $phone_numbers
     * @return array
     */
    public static function setPhones($resident_id, $phone_numbers)
    {
        $resident = Resident::findOne($resident_id) ?? null;

        if (!$resident) return ['error' => 1, 'data' => 'Отсутствует заявитель'];

        $contact = $resident->contact ?? null;

        if (!$contact) {
            $contact = new Contact();
            $contact->save();
        }

        $current_phones = Contact::getPhonesWithContact($contact->id);
        $new_phones = explode(',', str_replace(' ', '', $phone_numbers));

        Yii::info('Current phones: ' , 'test');
        Yii::info($current_phones, 'test');
        Yii::info('New phones: ' , 'test');
        Yii::info($new_phones, 'test');

        Yii::info(array_diff($current_phones, $new_phones), 'test');

        $for_delete = array_diff($current_phones, $new_phones); //Для удаления
        $for_add = array_diff($new_phones, $current_phones); //Для добавления

        $del_result = Phone::DeleteAll(['IN', 'number', $for_delete]);

        Yii::info('Del result: ' . $del_result, 'test');

        //Добавляем
        foreach ($for_add as $phone) {
            $model = new Phone([
                'contact_id' => $contact->id,
                'number' => Functions::formatPhone($phone),
            ]);
            if (!$model->save()){
                return ['error' => 1, 'data' => 'Ошибка сохранения номера телефона'];
                Yii::error($model->errors, '_error');
            }
        }

        return ['error' => 0, 'data' => 'Телефоны изменены успешно'];

    }

    public static function addPhone(Resident $resident, $phone_number)
    {
        if (!Phone::find()
        ->andWhere(['contact_id' => $resident->contact_id])
        ->andWhere(['number' => $phone_number])
        ->exists()) {
            $model = new Phone([
                'number' => $phone_number,
                'contact_id' => $resident->contact_id
            ]);
            if (!$model->save()){
                return ['error' => 1, 'data' => 'Ошибка сохранения телефона'];
                Yii::error($model->errors, '_error');
            }
        }

        return ['error' => 0, 'data' => 'Успешно'];
    }

}
