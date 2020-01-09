<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $file;
    public $files;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'xls, xlsx'],
            [
                ['files'],
                'file',
                'skipOnEmpty' => true,
                'maxFiles' => 5,
                'extensions' => 'pdf, jpg, jpeg, doc, docx, xls, xlsx, odt, odx, txt',
                'message' => 'Недопустимый формат файла'
            ],
        ];
    }

    /**
     * Загрузка одного файла
     * @param string $tmp_name
     * @return bool
     */
    public function upload($tmp_name)
    {
        Yii::info('this UploadForm', 'test');
        Yii::info('Temp name: ' . $tmp_name, 'test');
        if ($this->validate()) {
            if (!file_exists('uploads')) {
                mkdir('uploads');
            }
            $this->file->saveAs('uploads/' . $tmp_name . '.' . $this->file->extension);
            return true;
        } else {
            Yii::error($this->errors, 'error');
            return false;
        }
    }

    /**
     * Загрузка нескольких файлов
     * @return bool
     */
    public function uploads()
    {
        if ($this->validate()) {
            $tmp_dir = 'uploads/' . ((int)time() + rand(9999, 999999999));

            if (!is_dir($tmp_dir)) {
                mkdir($tmp_dir, 0777, true);
            }
            /** @var UploadedFile $file */
            foreach ($this->files as $file) {
                $file->saveAs($tmp_dir . '/' . $file->baseName . '.' . $file->extension);
            }
            return $tmp_dir; //Возвращаем путь к папке с файлами
        }
        return false;
    }
}