<?php

declare(strict_types=1);

namespace Lex\Yii2\Cycle\Validator;

use Cycle\ORM\ORMInterface;
use Yii;
use yii\validators\Validator;

final class UniqueValidator extends Validator
{
    public string $role;
    public ?string $field;
    private ORMInterface $orm;

    public function __construct(ORMInterface $orm, $config = [])
    {
        $this->orm = $orm;
        $this->role = '';
        $this->field = null;
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute): void
    {
        $repository = $this->orm->getRepository($this->role);
        $entity = $repository->findOne([$this->field ?: $attribute => $model->$attribute]);
        if ($entity) {
            $this->addError($model, $attribute, Yii::t('yii', '{attribute} is invalid.'));
        }
    }
}
