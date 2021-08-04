<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle;

use Spiral\Reactor\ClassDeclaration as BaseClassDeclaration;

final class ClassDeclaration extends BaseClassDeclaration
{
    private string $name;

    public function setName(string $name): BaseClassDeclaration
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
