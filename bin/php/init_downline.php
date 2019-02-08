<?php
/**
 * Executable script to create downline related data for development.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Res\Partner as EResPartner;
use Praxigento\Milc\Bonus\Api\Repo\Data\Sale\Order as ESaleOrder;

/** Maximal possible increment for date in seconds */
const DATE_INC_MAX = 9000; //max random increment in seconds
const PERCENT_DELETE = 16;
const PERCENT_RESTORE = 8;
const PERCENT_SET_TYPE = 20;
const TOTAL_ITEMS = 100;

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
     * Create customers.
     */
    $date = \DateTime::createFromFormat(Cfg::BEGINNING_OF_AGES_FORMAT, Cfg::BEGINNING_OF_AGES);
    /* IDs of the customers (all, active, inactive, deleted) */
    $mapAll = [];
    $mapDistr = [];
    $mapCust = [];
    $mapDeleted = [];
    $rootId = null;
    for ($i = 0; $i < TOTAL_ITEMS; $i++) {
        /* create new customer in Odoo */
        $customerId = createPartner($container);
        $mapAll[] = $customerId;
        /* add new customer to downline tree */
        echo "\nnew: $customerId";
        [$date, $rootId, $parentId, $isNotDistr] = downlineAddTo($container, $date, $mapDistr, $customerId, $rootId);
        if ($isNotDistr) {
            $mapCust[] = $customerId;
        } else {
            $mapDistr[] = $customerId;
        }
        echo "/$parentId (not distr: $isNotDistr).";

        /* change parent for random customer */
        changeParent($container, $date, $mapDistr, $rootId);

        /* delete random customer (see const PERCENT_DELETE) */
        [$date, $mapDistr, $mapDeleted] = deleteDistr($container, $date, $mapDistr, $mapDeleted, $rootId);

        /* restore random customer (see const PERCENT_RESTORE) */
        [$date, $mapDistr, $mapDeleted] = restoreDistr($container, $date, $mapDistr, $mapDeleted, $rootId);

        /* change client type (cust/distr) randomly (see const PERCENT_SET_TYPE) */
        [$date, $mapDistr, $mapCust, $mapDeleted] = changeType($container, $date, $mapDistr, $mapCust, $mapDeleted, $rootId);
        [$date] = registerCv($container, $date, $mapDistr, $mapCust);
    }

    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $em->flush();

    echo "\nDone.\n";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param \DateTime $date
 * @param int[] $mapDistr
 * @param int $customerId
 * @param int|null $rootId
 * @return array
 * @throws \Exception
 */
function downlineAddTo($container, $date, $mapDistr, $customerId, $rootId)
{
    /* prepare working data */
    if (is_null($rootId)) {
        $rootId = $customerId;
        $parentId = $customerId;
    } else {
        /* parent ID should not be equal to customer ID - we have a failures when we delete customers */
        do {
            $count = count($mapDistr) - 1;
            $key = random_int(0, $count);
            $parentId = $mapDistr[$key];
        } while ($parentId == $customerId);
    }
    /* 5% - new customer is not distributor */
    $isNotDistr = (random_int(1, 20) == 8);
    $mlmId = $customerId; // simple rule: MLM ID equals to ID

    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Add $srvDwnCustAdd */
    $srvDwnCustAdd = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Add::class);
    $reqAdd = new \Praxigento\Milc\Bonus\Api\Service\Client\Add\Request();
    $reqAdd->clientId = $customerId;
    $reqAdd->parentId = $parentId;
    $reqAdd->mlmId = $mlmId;
    $reqAdd->isNotDistributor = $isNotDistr;
    $date = dateModify($date);
    $reqAdd->date = $date;
    $respAdd = $srvDwnCustAdd->exec($reqAdd);

    return [$date, $rootId, $parentId, $isNotDistr];
}

/**
 * Update parent for random distributor.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param \DateTime $date
 * @param int[] $mapDistr
 * @param int $rootId
 * @return array
 * @throws \Exception
 */
function changeParent($container, $date, $mapDistr, $rootId)
{
    $count = count($mapDistr) - 1;
    if ($count > 1) {
        $keyDistr = random_int(0, $count);
        $clientId = $mapDistr[$keyDistr];
        $keyParent = random_int(0, $count);
        $parentIdNew = $mapDistr[$keyParent];
        if (
            ($parentIdNew != $clientId) &&
            ($clientId != $rootId)  // don't change parent for the root customer
        ) {
            /** @var \Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent $srvDwnChangeParent */
            $srvDwnChangeParent = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent::class);
            $reqChange = new \Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent\Request();
            $reqChange->clientId = $clientId;
            $reqChange->parentIdNew = $parentIdNew;
            $date = dateModify($date);
            $reqChange->date = $date;
            echo "\nchanged: $clientId: ";
            $respChange = $srvDwnChangeParent->exec($reqChange);
            $parentIdOld = $respChange->parentIdOld;
            echo "$parentIdOld => $parentIdNew.";
        }
    }
    return [$date];
}

/**
 * @param \Praxigento\Milc\Bonus\Api\Service\Client\SetType $container
 * @param \DateTime $date
 * @param int[] $mapDistr
 * @param int[] $mapCust
 * @param int[] $mapDeleted
 * @param int $rootId
 * @return array
 * @throws \Exception
 */
function changeType($container, $date, $mapDistr, $mapCust, $mapDeleted, $rootId)
{
    $shouldChange = randomPercent(PERCENT_SET_TYPE);
    if ($shouldChange) {
        $changeDistr = randomPercent(50);
        if ($changeDistr) {
            /* change type for distributor */
            $count = count($mapDistr) - 1;
            if ($count > 1) {
                $key = random_int(0, $count);
                $clientId = $mapDistr[$key];
            }
        } else {
            /* change type for customer */
            $count = count($mapCust) - 1;
            if ($count > 1) {
                $key = random_int(0, $count);
                $clientId = $mapCust[$key];
            }
        }
        /* there is data to change*/
        if (
            isset($clientId) &&
            ($clientId != $rootId) &&
            !in_array($clientId, $mapDeleted)
        ) {
            $req = new \Praxigento\Milc\Bonus\Api\Service\Client\SetType\Request();
            $req->clientId = $clientId;
            $req->isCustomer = $changeDistr;
            $date = dateModify($date);
            $req->date = $date;
            /** @var \Praxigento\Milc\Bonus\Api\Service\Client\SetType $srv */
            $srv = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\SetType::class);
            echo "\nset_type: $clientId, set_customer: $changeDistr";
            $srv->exec($req);
            /* fix maps */
            if ($changeDistr) {
                /* move distr to cust */
                unset($mapDistr[$key]);
                $mapDistr = array_values($mapDistr);
                $mapCust[] = $clientId;
            } else {
                /* move cust to distr */
                unset($mapCust[$key]);
                $mapCust = array_values($mapCust);
                $mapDistr[] = $clientId;
            }
            echo ".";
        }
    }

    return [$date, $mapDistr, $mapCust, $mapDeleted];
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param \DateTime $date
 * @param int[] $mapDistr
 * @param int[] $mapDeleted
 * @param int $rootId
 * @return array
 * @throws \Exception
 */
function deleteDistr($container, $date, $mapDistr, $mapDeleted, $rootId)
{
    $shouldDelete = randomPercent(PERCENT_DELETE);
    if ($shouldDelete) {
        $count = count($mapDistr) - 1;
        if ($count > 1) {
            /* there are distributors to delete */
            $key = random_int(0, $count);
            $clientId = $mapDistr[$key];
            if ($clientId != $rootId) {
                /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Delete $srvDelete */
                $srvDelete = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Delete::class);
                $req = new \Praxigento\Milc\Bonus\Api\Service\Client\Delete\Request();
                $req->clientId = $clientId;
                $date = dateModify($date);
                $req->date = $date;
                echo "\ndeleted: $clientId";
                $resp = $srvDelete->exec($req);
                unset($mapDistr[$key]);
                $mapDistr = array_values($mapDistr);
                $mapDeleted[] = $clientId;
                echo ".";
            }
        }
    }
    return [$date, $mapDistr, $mapDeleted];
}

function restoreDistr($container, $date, $mapDistr, $mapDeleted, $rootId)
{
    $shouldRestore = randomPercent(PERCENT_RESTORE);
    if ($shouldRestore) {
        $countDel = count($mapDeleted) - 1;
        $countDistr = count($mapDistr) - 1;
        if ($countDel > 1) {
            /* there are distributors to delete */
            $keyCust = random_int(0, $countDel);
            $clientId = $mapDeleted[$keyCust];
            $keyParent = random_int(0, $countDistr);
            $parentId = $mapDistr[$keyParent];
            if (
                ($clientId != $rootId) &&
                ($clientId != $parentId)
            ) {
                /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Restore $srvRestore */
                $srvRestore = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Restore::class);
                $req = new \Praxigento\Milc\Bonus\Api\Service\Client\Restore\Request();
                $req->clientId = $clientId;
                $req->parentId = $parentId;
                $date = dateModify($date);
                $req->date = $date;
                echo "\nrestored: $clientId";
                $resp = $srvRestore->exec($req);
                unset($mapDeleted[$keyCust]);
                $mapDeleted = array_values($mapDeleted);
                $mapDistr[] = $clientId;
                echo ".";
            }
        }
    }
    return [$date, $mapDistr, $mapDeleted];
}

/**
 * Create new partner in Flectra related table.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @return int
 */
function createPartner(\Psr\Container\ContainerInterface $container): int
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $partner = new EResPartner();
    $em->persist($partner);
    $em->flush($partner);
    $result = $partner->id;
    return $result;
}

/**
 * Create new sale order in Flectra related table.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @return int
 */
function createSale(\Psr\Container\ContainerInterface $container): int
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $sale = new ESaleOrder();
    $em->persist($sale);
    $em->flush($sale);
    $result = $sale->id;
    return $result;
}

function registerCv($container, $date, $mapDistr, $mapCust)
{
    /** @var \Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv $srvRegister */
    $srvRegister = $container->get(\Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv::class);

    /* one CV movement for every 10 clients */
    $countDistrs = count($mapDistr);
    $countCusts = count($mapCust);
    $total = $countDistrs + $countCusts;
    $movements = intdiv($total, 10);
    /* if total < 10 then 25% for one movement */
    if (($movements < 1) && randomPercent(25))
        $movements = 1;
    /* make CV movements */
    /* 75% - should be a movement for distributor */
    $normDistr = $countDistrs * 75;
    $normCust = $countCusts * 25;
    $percentDistr = round(($normDistr / ($normDistr + $normCust)) * 100);
    for ($i = 0; $i < $movements; $i++) {
        /* add source sale order */
        $saleId = createSale($container);

        /* register CV */
        $isDistr = randomPercent($percentDistr);
        if ($isDistr) {
            $key = random_int(0, $countDistrs - 1);
            $clientId = $mapDistr[$key];
        } else {
            $key = random_int(0, $countCusts - 1);
            $clientId = $mapCust[$key];
        }
        /* random amount of CV (15%: 30.00 - 100.00; 85%: 100.00 - 250.00) */
        $amount = randomCvAmount();
        $isAutoship = randomPercent(70); // 70% - is autoship
        $date = dateModify($date);
        $req = new \Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv\Request();
        $req->clientId = $clientId;
        $req->volume = $amount;
        $req->date = $date;
        if ($isAutoship)
            $req->isAutoship = true;
        $req->sourceId = $saleId;
        $req->sourceType = Cfg::CV_REG_SOURCE_SALE;
        echo "\nCV move: $amount CV to $clientId";
        $srvRegister->exec($req);
        echo ".";
        /* 5% for backward movement */
        $needBackward = randomPercent(5);
        if ($needBackward) {
            $date = dateModify($date);
            $req->date = $date;
            $req->volume = 0 - $amount;
            $req->sourceType = Cfg::CV_REG_SOURCE_SALE_BACK;
            echo "\nCV backward move: $amount CV from $clientId";
            $srvRegister->exec($req);
            echo ".";
        }
    }

    return [$date];
}

/**
 * Add random delta to given date.
 *
 * @param \DateTime $date
 * @return \DateTime
 * @throws \Exception
 */
function dateModify($date)
{
    $seconds = random_int(0, DATE_INC_MAX);
    $date->modify("+$seconds seconds");
    return $date;
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

/**
 * CV amount:
 *  * 15%: 30.00 - 100.00
 *  * 85%: 100.00 - 250.00
 *
 * @return float
 * @throws \Exception
 */
function randomCvAmount(): float
{
    $needSmallAmnt = randomPercent(15);
    if ($needSmallAmnt) {
        /* 15%: 30.00 - 100.00 */
        $result = random_int(3000, 10000) / 100;
    } else {
        /* 85%: 100.00 - 250.00 */
        $result = random_int(10000, 25000) / 100;
    }

    return $result;
}