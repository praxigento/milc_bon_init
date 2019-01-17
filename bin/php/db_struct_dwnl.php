<?php
/**
 * Executable script to create downline related database structures.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Get DI container then populate database schema with DEM'ed entities.
 */
try {
    /**
     * Setup IoC-container.
     */
    $app = \Praxigento\Milc\Bonus\App::getInstance();
    $container = $app->getContainer();

    /**
     * Init DB connection & start transaction.
     */
    /** @var \TeqFw\Lib\Db\Api\Connection\Schema $conn */
    $conn = $container->get(\TeqFw\Lib\Db\Api\Connection\Schema::class);
    $conn->beginTransaction();

    /**
     * Read current DB Schema using Doctrine.
     */
    /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $man */
    $man = $conn->getSchemaManager();
    /** @var \Doctrine\DBAL\Schema\Schema $schema */
    $schema = $man->createSchema();

    /**
     * Add MILC tables to the Schema.
     */
    /** @var \TeqFw\Lib\Dem\Parser $parser */
    $parser = $container->get(\TeqFw\Lib\Dem\Parser::class);
    /** @var \TeqFw\Lib\Dem\Api\Helper\Ddl\Entity $ddl */
    $ddl = $container->get(\TeqFw\Lib\Dem\Api\Helper\Ddl\Entity::class);

    $json = readJsonDwn();
    $collection = $parser->parseJson($json);
    foreach ($collection->items as $entity) {
        $ddl->create($schema, $entity);
    }

    $json = readJsonBon();
    $collection = $parser->parseJson($json);
    foreach ($collection->items as $entity) {
        $ddl->create($schema, $entity);
    }

    /**
     * Persist tables into DB.
     */
    /** @var \Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer $sync */
    $sync = $container->get(\Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer::class);
    $sync->updateSchema($schema);

    $conn->commit();

    echo "\nDone.";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

/**
 * Load DEM JSON from file.
 * @return string
 */
function readJsonDwn()
{
    $file = __DIR__ . '/../../data/dem/dwn.json';
    $result = file_get_contents($file);
    return $result;
}

/**
 * Load DEM JSON from file.
 * @return string
 */
function readJsonBon()
{
    $file = __DIR__ . '/../../data/dem/bon.json';
    $result = file_get_contents($file);
    return $result;
}