<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle\Provider;

use Cycle\Annotated;
use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\Registry;
use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;
use Yii;

class FileProvider implements ProviderInterface
{
    /** @var array The connection settings to the database. */
    public array $dbal;
    /** @var array Paths to directories that contain entities. */
    public array $entities;
    /** @var array Paths to directories that contain migrations. */
    public array $migrations;
    /** @var string Name of the table that contains information about migrations. Default "migrations" */
    public string $migrationTable;
    /** @var string The path for generated migrations. */
    public string $migrationPath;
    /** @var string The namespace for generated migrations. */
    public string $migrationNamespace;

    private ?DatabaseManager $db;

    public function __construct()
    {
        $this->db = null;
        $this->dbal = [];
        $this->entities = [];
        $this->migrations = [];
        $this->migrationTable = 'migration';
    }


    public function getDbal(): DatabaseManager
    {
        if ($this->db === null) {
            $this->db = new DatabaseManager(
                new DatabaseConfig($this->dbal)
            );
        }
        return $this->db;
    }

    public function getFactory(DatabaseManager $dbal): FactoryInterface
    {
        return new Factory($dbal);
    }

    /**
     * @return array
     */
    public function getEntities(): array
    {
        $entities = [];
        foreach ($this->entities as $entity) {
            $entities[] = Yii::getAlias($entity);
        }
        return $entities;
    }

    public function getSchema(): SchemaInterface
    {
        $finder = (new Finder())
            ->files()
            ->in($this->getEntities());
        $classLocator = new ClassLocator($finder);
        $schema = (new Compiler())->compile(
            new Registry($this->getDbal()),
            [
                new Annotated\Embeddings($classLocator), // register embeddable entities
                new Annotated\Entities($classLocator), // register annotated entities
                new ResetTables(), // re-declared table schemas (remove columns)
                new GenerateRelations(), // generate entity relations
                new ValidateEntities(), // make sure all entity schemas are correct
                new RenderTables(), // declare table schemas
                new RenderRelations(), // declare relation keys and indexes
                new GenerateTypecast(), // typecast non string columns
            ]
        );

        return new Schema($schema);
    }

    public function getMigrationConfig(): MigrationConfig
    {
        $directories = [];
        foreach ($this->migrations as $migration) {
            $directories[] = Yii::getAlias($migration);
        }
        return new MigrationConfig(
            [
                'directories' => $directories,
                'table' => $this->migrationTable,
                'namespace' => $this->migrationNamespace
            ]
        );
    }
}