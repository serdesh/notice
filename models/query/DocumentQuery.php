<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\Document]].
 *
 * @see \app\models\Document
 */
class DocumentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\Document[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Document|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
