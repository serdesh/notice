<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\Message]].
 *
 * @see \app\models\Message
 */
class MessageQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\Message[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Message|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
