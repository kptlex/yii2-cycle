<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle;

use DateTime;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Migrations\Config\MigrationConfig;
use Spiral\Migrations\Exception\RepositoryException;
use Spiral\Migrations\MigrationInterface;
use Spiral\Migrations\RepositoryInterface;
use Spiral\Migrations\State;
use Spiral\Tokenizer\Reflection\ReflectionFile;
use yii\helpers\Inflector;
use Generator;
use ReflectionClass;
use ReflectionException;
use Throwable;

use function get_class;

/**
 * Stores migrations as files.
 */
final class FileRepository implements RepositoryInterface
{
    // Migrations file name format. This format will be used when requesting new migration filename.
    public const FILENAME_FORMAT = 'V%s_%s.php';
    // Timestamp format for files.
    private const TIMESTAMP_FORMAT = 'YmdHis';
    public array $directories = [];
    /**
     * Required when multiple migrations added at once.
     * @var int
     */
    private int $chunkID = 0;
    private FactoryInterface $factory;
    private FilesInterface $files;
    private MigrationConfig $config;


    /**
     * @param MigrationConfig $config
     * @param FactoryInterface|null $factory
     */
    public function __construct(MigrationConfig $config, FactoryInterface $factory = null)
    {
        $this->config = $config;
        $this->files = new Files();
        $this->factory = $factory ?? new Container();
    }

    /**
     * {@inheritdoc}
     * @throws ReflectionException
     * @throws Throwable
     */
    public function registerMigration(string $name, string $class, string $body = null): string
    {
        if (empty($body) && !class_exists($class)) {
            throw new RepositoryException(
                "Unable to register migration '{$class}', representing class does not exists"
            );
        }

        foreach ($this->getMigrations() as $migration) {
            if (get_class($migration) === $class) {
                throw new RepositoryException(
                    "Unable to register migration '{$class}', migration already exists"
                );
            }
            if ($migration->getState()->getName() === $name) {
                throw new RepositoryException(
                    "Unable to register migration '{$name}', migration under the same name already exists"
                );
            }
        }

        if (empty($body)) {
            //Let's read body from a given class filename
            $body = $this->files->read((new ReflectionClass($class))->getFileName());
        }

        $filename = $this->createFilename($name);

        //Copying
        $this->files->write($filename, $body, FilesInterface::READONLY, true);

        return $filename;
    }

    /**
     * {@inheritdoc}
     * @throws Throwable
     */
    public function getMigrations(): array
    {
        $migrations = [];

        foreach ($this->getFiles() as $f) {
            if (!class_exists($f['class'], false)) {
                require_once($f['filename']);
            }
            /** @var MigrationInterface $migration */
            $migration = $this->factory->make($f['class']);
            $migrations[$f['created']->getTimestamp() . $f['chunk']] = $migration->withState(
                new State($f['name'], $f['created'])
            );
        }

        ksort($migrations);

        return $migrations;
    }

    /**
     * Internal method to fetch all migration filenames.
     */
    private function getFiles(): Generator
    {
        $this->directories[] = $this->config->getDirectory();
        foreach ($this->directories as $directory) {
            foreach ($this->files->getFiles($directory, '*.php') as $filename) {
                if (!preg_match('/V[\d]+_(.*)/', $filename)) {
                    continue;
                }

                $reflection = new ReflectionFile($filename);
                $tmpName = substr(basename($filename), 1);

                $definition = explode('_', $tmpName);

                yield [
                    'filename' => $filename,
                    'class' => $reflection->getClasses()[0],
                    'created' => DateTime::createFromFormat(
                        self::TIMESTAMP_FORMAT,
                        $definition[0]
                    ),
                    'chunk' => $this->chunkID,
                    'name' => str_replace('.php', '', basename($filename)),
                ];
            }
        }
    }

    /**
     * Request new migration filename based on user input and current timestamp.
     *
     * @param string $name
     * @return string
     */
    public function createFilename(string $name): string
    {
        $name = Inflector::tableize($name);

        $filename = sprintf(
            self::FILENAME_FORMAT,
            date(self::TIMESTAMP_FORMAT),
            $name
        );

        return $this->files->normalizePath(
            $this->config->getDirectory() . FilesInterface::SEPARATOR . $filename
        );
    }
}
