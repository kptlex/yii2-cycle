<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle\Tests\Entity;

use Cycle\Annotated\Annotation as ORM;

/**
 * Class Test
 * @package Lex\Yii\Cycle\Tests\Entity
 * @ORM\Entity(table="test")
 */
class Test
{
    /**
     * @ORM\Column(type="primary")
     */
    private ?int $id;
    /**
     * @ORM\Column(type="string")
     */
    private ?string $name;

    /**
     * Test constructor.
     */
    public function __construct()
    {
        $this->id = null;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}