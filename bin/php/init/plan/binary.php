<?php
/**
 * Executable script to init BINARY configuration for bonuses.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Praxigento\Milc\Bonus\Api\Config as Cfg;

/**
 * Get DI container then populate database schema with DEM'ed entities.
 */
try {
    /**
     * Setup IoC-container.
     */
    $app = \Praxigento\Milc\Bonus\App::getInstance();
    $container = $app->getContainer();

    /** @var \Praxigento\Milc\Bonus\Api\Helper\Init\Bonus $hlpInit */
    $hlpInit = $container->get(\Praxigento\Milc\Bonus\Api\Helper\Init\Bonus::class);
    /** @var \TeqFw\Lib\Db\Api\Connection\Main $conn */
    $conn = $container->get(\TeqFw\Lib\Db\Api\Connection\Main::class);
    $conn->beginTransaction();

    $plan = $hlpInit->plan();
    $planId = $plan->id;
    $ranks = $hlpInit->planRanks($planId);
    $suite = $hlpInit->suiteWeek($planId, 'Binary suite (weekly).');
    $suiteId = $suite->id;

    $typeIds = $hlpInit->calcTypes();
    $calcIds = $hlpInit->suiteCalcsBinary($suiteId, $typeIds);
    $calcIdRanks = $calcIds[Cfg::CALC_TYPE_RANK_QUAL];
    $hlpInit->qualRules($calcIdRanks, $ranks);
//    $calcIdLevels = $calcIds[Cfg::CALC_TYPE_COMM_LEVEL_BASED];
//    $hlpInit->commLevels($calcIdLevels, $ranks);

    $conn->commit();

    echo "\nDone.\n";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}