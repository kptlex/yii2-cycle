<?php

declare(strict_types=1);

namespace Lex\Yii2\Cycle\Validator;

use Cycle\ORM\ORMInterface;
use Yii;
use yii\validators\Validator;

final class EntityValidator extends Validator
{
    public string $role;
    private ORMInterface $orm;

    public function __construct(ORMInterface $orm, $config = [])
    {
        $this->orm = $orm;
        $this->role = '';
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    public function validateAttribute($model, $attribute): void
    {
        if ($model->$attribute) {
            $repository = $this->orm->getRepository($this->role);
            $entity = $repository->findByPK($model->$attribute);
            if (!$entity) {
                $this->addError($model, $attribute, $this->message, [
                    'attribute' => $model->getAttributeLabel($attribute)
                ]);
            }
        }
    }
}
