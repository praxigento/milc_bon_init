<?php
/**
 * Executable script to create downline related data for development.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';

use Praxigento\Milc\Bonus\Api\Repo\Data\Res\Partner as EResPartner;
/** Maximal possible increment for date in seconds */
const DATE_INC_MAX = 3600;
const PERCENT_DELETE = 16;
const PERCENT_RESTORE = 8;
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
     * Get active objects (managers, services, etc.).
     */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Add $srvDwnCustAdd */
    $srvDwnCustAdd = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Downline\Add::class);
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Downline\ChangeParent $srvDwnChangeParent */
    $srvDwnChangeParent = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Downline\ChangeParent::class);
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Delete $srvDwnDelete */
    $srvDwnDelete = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Downline\Delete::class);

    /**
     * Create customers.
     */
    $date = new \DateTime();
    $date->modify('-100 days');
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
        [$date, $rootId, $parentId, $isNotDistr] = downlineAddTo($container, $date, $mapDistr, $customerId, $rootId);
        if ($isNotDistr) {
            $mapCust[] = $customerId;
        } else {
            $mapDistr[] = $customerId;
        }
        echo "\nnew: $customerId/$parentId (not distr: $isNotDistr).";

        /* change parent for random customer */
        changeParent($container, $date, $mapDistr, $rootId);

        /* delete random customer (16%) */
        [$date, $mapDistr, $mapDeleted] = deleteDistr($container, $date, $mapDistr, $mapDeleted, $rootId);

        /* restore random customer */
        [$date, $mapDistr, $mapDeleted] = restoreDistr($container, $date, $mapDistr, $mapDeleted, $rootId);

        if (0 > 1) {
            /**
             * Random parent update for existing customer.
             */
            $keyCust = random_int(0, $count);
            $memberId = $mapDistr[$keyCust];
            $keyParent = random_int(0, $count);
            $parentIdNew = $mapDistr[$keyParent];
            if (
                ($parentIdNew != $memberId) &&
                ($memberId != $rootId)  // don't change parent for the root customer
            ) {
                $reqChange = new \Praxigento\Milc\Bonus\Api\Service\Client\Downline\ChangeParent\Request();
                $reqChange->customerId = $memberId;
                $reqChange->parentIdNew = $parentIdNew;
                $seconds = random_int(0, 3600);
                $date->modify("+$seconds seconds");
                $reqChange->date = $date;
                $respChange = $srvDwnChangeParent->exec($reqChange);
                $parentIdOld = $respChange->parentIdOld;
                echo "\nchanged: $memberId: $parentIdOld => $parentIdNew.";
            }
            /* delete customer with 25%*/
            if (random_int(1, 4) == 2) {
                $keyDel = random_int(0, $count);
                $custIdDel = $mapDistr[$keyDel];
                if ($custIdDel != $rootId) {
                    $reqDel = new \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Delete\Request();
                    $reqDel->customerId = $custIdDel;
                    $seconds = random_int(0, 3600);
                    $date->modify("+$seconds seconds");
                    $reqDel->date = $date;
                    echo "\ndelete: $custIdDel.";
                    $respDel = $srvDwnDelete->exec($reqDel);
                    /* remove key from existing customers registry */
                    unset($mapDistr[$keyDel]);
                    $mapDistr = array_values($mapDistr);
                }
            }

        }
    }

    $em->flush();

    echo "\nDone.";
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
    $mlmId = ($isNotDistr) ? null : $customerId; // simple rule: MLM ID equals to ID for distributors

    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Add $srvDwnCustAdd */
    $srvDwnCustAdd = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Downline\Add::class);
    $reqAdd = new \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Add\Request();
    $reqAdd->customerId = $customerId;
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
        $custId = $mapDistr[$keyDistr];
        $keyParent = random_int(0, $count);
        $parentIdNew = $mapDistr[$keyParent];
        if (
            ($parentIdNew != $custId) &&
            ($custId != $rootId)  // don't change parent for the root customer
        ) {
            /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Downline\ChangeParent $srvDwnChangeParent */
            $srvDwnChangeParent = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Downline\ChangeParent::class);
            $reqChange = new \Praxigento\Milc\Bonus\Api\Service\Client\Downline\ChangeParent\Request();
            $reqChange->customerId = $custId;
            $reqChange->parentIdNew = $parentIdNew;
            $date = dateModify($date);
            $reqChange->date = $date;
            $respChange = $srvDwnChangeParent->exec($reqChange);
            $parentIdOld = $respChange->parentIdOld;
            echo "\nchanged: $custId: $parentIdOld => $parentIdNew.";
        }
    }
    return [$date];
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
            $custId = $mapDistr[$key];
            if ($custId != $rootId) {
                /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Delete $srvDelete */
                $srvDelete = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Downline\Delete::class);
                $req = new \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Delete\Request();
                $req->customerId = $custId;
                $date = dateModify($date);
                $req->date = $date;
                $resp = $srvDelete->exec($req);
                unset($mapDistr[$key]);
                $mapDistr = array_values($mapDistr);
                $mapDeleted[] = $custId;
                echo "\ndeleted: $custId.";
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
            $custId = $mapDeleted[$keyCust];
            $keyParent = random_int(0, $countDistr);
            $parentId = $mapDistr[$keyParent];
            if (
                ($custId != $rootId) &&
                ($custId != $parentId)
            ) {
                /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Restore $srvRestore */
                $srvRestore = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Downline\Restore::class);
                $req = new \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Restore\Request();
                $req->customerId = $custId;
                $req->parentId = $parentId;
                $date = dateModify($date);
                $req->date = $date;
                $resp = $srvRestore->exec($req);
                unset($mapDeleted[$keyCust]);
                $mapDeleted = array_values($mapDeleted);
                $mapDistr[] = $custId;
                echo "\nrestored: $custId.";
            }
        }
    }
    return [$date, $mapDistr, $mapDeleted];
}

/**
 * Create new partner in Odoo related table.
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