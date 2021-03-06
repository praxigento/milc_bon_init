<?php
/**
 * Executable script to emulate Binary bonus calculation.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../../vendor/autoload.php';

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Helper\Period as HPeriod;

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

    $conn->beginTransaction();

    /* start from the beginning of the ages and get one month to process */
    $date = \DateTime::createFromFormat(Cfg::BEGINNING_OF_AGES_FORMAT, Cfg::BEGINNING_OF_AGES);
    $dateFrom = $hlpPeriod->getTimestampFrom($date, HPeriod::TYPE_WEEK);
    $dateTo = $hlpPeriod->getTimestampTo($date, HPeriod::TYPE_WEEK);

    /* get developer suite of calcs */
    $suite = $hlpCalc->getSuite();
    $calcCollect = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_CV_COLLECT);
    $calcTree = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_TREE_BINARY);
    $calcPv = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_CV_GROUPING_PV);
    $calcRank = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_RANK_QUAL);
    $calcComm = $hlpCalc->getSuiteCalc($suite->id, Cfg::CALC_TYPE_COMM_BINARY);

    /* register new pool */
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
    /** Step 4: Rank Qualification. */
    $poolCalcRank = $hlpCalc->registerPoolCalc($poolId, $calcRank->id);
    $hlpCalc->step04Rank($poolCalcRank->id, $poolCalcTree->id);
    /** Step 5: Level Based Commissions. */
//    $poolCalcComm = $hlpCalc->registerPoolCalc($poolId, $calcComm->id);
//    $hlpCalc->step05Comm($poolCalcComm->id, $poolCalcTree->id, $poolCalcRank->id);


    $conn->commit();

    echo "\nDone.\n";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}