<?php
/**
 * Executable script to emulate chain of the Unilevel bonus calculations.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Helper\Period as HPeriod;

/**
 * Get DI container then populate database schema with DEM'ed entities.
 */
try {
    /**
     * Setup IoC-container.
     */
    $app = \Praxigento\Milc\Bonus\App::getInstance();
    $container = $app->getContainer();

    /** @var \TeqFw\Lib\Db\Api\Connection\Main $conn */
    $conn = $container->get(\TeqFw\Lib\Db\Api\Connection\Main::class);
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Emulate\Common $hlpCommon */
    $hlpCommon = $container->get(\Praxigento\Milc\Bonus\Api\Helper\Emulate\Common::class);
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Period $hlpPeriod */
    $hlpPeriod = $container->get(\Praxigento\Milc\Bonus\Api\Helper\Period::class);
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Emulate\Calc $hlpCalc */
    $hlpCalc = $container->get(\Praxigento\Milc\Bonus\Api\Helper\Emulate\Calc::class);

    /* start from the beginning of the ages */
    $date = \DateTime::createFromFormat(Cfg::BEGINNING_OF_AGES_FORMAT, Cfg::BEGINNING_OF_AGES);
    $dateMax = $hlpCalc->getDateMax();
    $i = 0;
    $maxInc = 3600 * 24;
    $suite = $hlpCalc->getSuite();
    $calcCollect = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_CV_COLLECT);
    $calcTree = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_TREE_NATURAL);
    $calcPv = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_CV_GROUPING_PV);
    $calcQual = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_RANK_QUAL);
    $calcComm = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_COMM_LEVEL_BASED);
    do {
        $date = $hlpCommon->dateModify($date, $maxInc);
        $dateFrom = $hlpPeriod->getTimestampFrom($date, HPeriod::TYPE_MONTH);
        $dateTo = $date->format(Cfg::FORMAT_DATETIME);
        echo "\niteration $i: $dateFrom - $dateTo.";
        $conn->beginTransaction();

        /* register new race */
        $period = $hlpCalc->registerPeriod($dateFrom, $suite->id);
        $pool = $hlpCalc->registerPool($period->id, $dateTo);
        $poolId = $pool->id;
        /* STEP 1: collect CV for given period (from-to)*/
        $poolCalcCollect = $hlpCalc->registerPoolCalc($poolId, $calcCollect->id);
        $hlpCalc->step01CvCollect($poolCalcCollect->id, $dateFrom, $dateTo);
        /** STEP 2: Compose tree (just copy plain tree for the end of the period). */
        $poolCalcTree = $hlpCalc->registerPoolCalc($poolId, $calcTree->id);
        $hlpCalc->step02Tree($poolCalcTree->id, $dateTo);
        /** Step 3: PV grouping. */
        $poolCalcPv = $hlpCalc->registerPoolCalc($poolId, $calcPv->id);
        $hlpCalc->step03GroupPv($poolCalcPv->id, $poolCalcCollect->id, $poolCalcTree->id);
        /** Step 4: Qualification. */
        $poolCalcQual = $hlpCalc->registerPoolCalc($poolId, $calcQual->id);
        $hlpCalc->step04Rank($poolCalcQual->id, $poolCalcTree->id);
        /** Step 5: Level Based Commissions. */
        $poolCalcComm = $hlpCalc->registerPoolCalc($poolId, $calcComm->id);
        $hlpCalc->step05Comm($poolCalcComm->id, $poolCalcTree->id, $poolCalcQual->id);


        $conn->commit();
    } while (($date < $dateMax) && (++$i < 1000));

    echo "\nDone.\n";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

