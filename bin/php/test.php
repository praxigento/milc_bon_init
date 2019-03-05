<?php
/**
 * Executable script for development.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'commons.php';

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Response as AResponse;

/**
 * Get DI container then populate database schema with DEM'ed entities.
 */
try {
    /**
     * Setup IoC-container.
     */
    $app = \Praxigento\Milc\Bonus\App::getInstance();
    $container = $app->getContainer();
    $typeCode = Cfg::CALC_TYPE_CV_COLLECT;

    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get $srv */
    $srv = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get::class);
    $req = new ARequest();
    /** @var AResponse $resp */
    $resp = $srv->exec($req);

    echo "\nDone.\n";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
