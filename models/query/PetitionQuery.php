<?php

namespace app\models\query;

use app\models\Users;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\Petition]].
 *
 * @see \app\models\Petition
 */
class PetitionQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\Petition[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Petition|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Обращения для компании
     * @return ActiveQuery
     */
    public function fromCompany()
    {
        return parent::andWhere(['company_id' => Users::getCompanyIdForUser()]);
    }

    /**
     * Новые обращения
     * @return ActiveQuery
     */
    public function statusNew()
    {
        return parent::andWhere(['status_id' => 1]);
    }
}
