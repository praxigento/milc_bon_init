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

    /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $man */
    $man = $conn->getSchemaManager();
    /** @var \Doctrine\DBAL\Schema\Schema $schema */
    $schema = $man->createSchema();

    $tableName = 'dwn_tree';
    $hasTable = $schema->hasTable($tableName);
    if ($hasTable) {
        $table = $schema->dropTable($tableName);
    }
    $table = $schema->createTable($tableName);
    $table->addColumn('user_ref', 'integer', ['unsigned' => true]);
    $table->addColumn('parent_ref', 'integer', ['length' => 32]);
    $table->setPrimaryKey(['user_ref', 'parent_ref']);


    /* persist tables into DB */
    /** @var \Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer $sync */
    $sync = $container->get(\Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer::class);
    $sync->updateSchema($schema);

    $conn->commit();

    echo "\nDone.";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}