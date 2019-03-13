<?php
/**
 * Executable script to create binary downline tree for development.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Service\Client\Add as SrvAdd;

/** Maximal possible increment for date in seconds */
const DATE_INC_MAX = Cfg::BEGINNING_OF_AGES_INC_MAX; //max random increment in seconds
const PERCENT_ADD_TO_LEFT_LEG = 50;
const PERCENT_CLAWBACK = 10;
const PERCENT_NEW_CLIENT_IS_NOT_DISTR = 5;
const PERCENT_PARENT_CHANGE = 20;
const PERCENT_TYPE_CHANGE = 20;
const TOTAL_CLIENTS = 80;

/**
 * Get DI container then start DB transaction to perform changes in data.
 */
try {
    /**
     * Setup IoC-container.
     */
    $app = \Praxigento\Milc\Bonus\App::getInstance();
    $container = $app->getContainer();
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Emulate\Activity $hlpAct */
    $hlpAct = $container->get(\Praxigento\Milc\Bonus\Api\Helper\Emulate\Activity::class);
    /** @var \TeqFw\Lib\Db\Api\Connection\Main $conn */
    $conn = $container->get(\TeqFw\Lib\Db\Api\Connection\Main::class);
    $conn->beginTransaction();

    /**
     * Create customers.
     */
    $date = \DateTime::createFromFormat(Cfg::BEGINNING_OF_AGES_FORMAT, Cfg::BEGINNING_OF_AGES);
    /* IDs of the customers (all, active, inactive, deleted) */
    $mapAll = [];
    $mapDistr = [];
    $mapCust = [];
    $rootId = null;
    for ($i = 0; $i < TOTAL_CLIENTS; $i++) {
        /* add new customer to downline tree */
        [$date, $rootId, $clientId, $parentId, $isNotDistr]
            = $hlpAct->clientCreate(SrvAdd::TMP_TREE_TYPE_BINARY, PERCENT_NEW_CLIENT_IS_NOT_DISTR, PERCENT_ADD_TO_LEFT_LEG);

        /* change client type (cust/distr)  */
        $needClientTypeChange = randomPercent(PERCENT_TYPE_CHANGE);
        if ($needClientTypeChange) {
            [$clientId, $typeOld, $typeNew] = $hlpAct->clientChangeType(false);
        }

        /* add sale orders with CV/ACV */
        $sales = $hlpAct->salesAdd();
        $totals = count($sales);
        echo "\nsales: $totals:";
        foreach ($sales as $saleId => $saleData) {
            $cv = number_format($saleData[0], 2);
            $isAuto = $saleData[1] ? 'yes' : 'no';
            $clientId = $saleData[2];
            echo "\n\t#$saleId: cv: $cv, autoship: $isAuto, client: #$clientId;";
        }

        /* add clawbacks for sale orders */
        $needClawback = randomPercent(PERCENT_CLAWBACK);
        if ($needClawback) {
            [$saleId, $cv, $isAutoship, $clientId] = $hlpAct->salesClawback();
            if ($saleId) {
                echo "\nclawback: sale #$saleId (cv: $cv, autoship: $isAuto, client: #$clientId).";
            }
        }
    }

    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $em->flush();

    $conn->commit();

    echo "\nDone.\n";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

/**
 * Return 'true' with probability of $percent %.
 *
 * @param int $percent
 * @return bool
 * @throws \Exception
 */
function randomPercent(int $percent): bool
{
    $result = (random_int(0, 99) < $percent);
    return $result;
}