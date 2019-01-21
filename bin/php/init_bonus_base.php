<?php
/**
 * Executable script to init base configuration for bonuses.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';

use Doctrine\ORM\EntityManager as DocEnMgr;
use Doctrine\ORM\Tools\Setup as DocSetup;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Calc\Type as EBonBaseCalcType;

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
     * Init Doctrine ORM.
     */
    $isDevMode = true;
    $config = DocSetup::createAnnotationMetadataConfiguration(array(__DIR__ . "/../../src/php"), $isDevMode);
    $cfg = $container->get(\TeqFw\Lib\Db\Api\Data\Cfg\Db::class);
    $connectionParams = (array)$cfg;
    $em = DocEnMgr::create($connectionParams, $config);

    $levelBased = new EBonBaseCalcType();
    $levelBased->code = 'LEVEL_BASED';
    $levelBased->note = 'Levels Based Commissions';
    $em->persist($levelBased);

    $em->flush();
    echo "\nDone.";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}