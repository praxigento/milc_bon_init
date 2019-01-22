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
    /** @var \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Add $srvDwnCustAdd */
    $srvDwnCustAdd = $container->get(\Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Add::class);
    /** @var \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\ChangeParent $srvDwnChangeParent */
    $srvDwnChangeParent = $container->get(\Praxigento\Milc\Bonus\Api\Service\Downline\Tree\ChangeParent::class);
    /** @var \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Delete $srvDwnDelete */
    $srvDwnDelete = $container->get(\Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Delete::class);

    /**
     * Create 1000 users.
     */
    $date = new \DateTime();
    $date->modify('-100 days');
    /* IDs of the existing customers */
    $custExist = [];

    for ($i = 0; $i < 100; $i++) {
        /* create new customer in base DB structure */
        $partner = new EResPartner();
        $em->persist($partner);
        $customerId = $partner->id;
        $custExist[] = $customerId;
        $em->flush($partner);

        /* collect customer data (should be performed in outside FELICS ) */
        $count = count($custExist) - 1;
        if ($i == 0) {
            $parentId = $customerId;
        } else {
            /* parent ID should not be equal to customer ID - we have a failures when we delete customers */
            do {
                $key = random_int(0, $count);
                $parentId = $custExist[$key];
            } while ($parentId == $customerId);
        }
        $mlmId = $customerId;

        /* register new customer in downline */
        $reqAdd = new \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Add\Request();
        $reqAdd->customerId = $customerId;
        $reqAdd->parentId = $parentId;
        $reqAdd->mlmId = $mlmId;
        $seconds = random_int(0, 3600);
        $date->modify("+$seconds seconds");
        $reqAdd->date = $date;
        echo "\nadd: $customerId/$parentId.";
        $respAdd = $srvDwnCustAdd->exec($reqAdd);

        if ($count > 1) {
            /**
             * Random parent update for existing customer.
             */
            $keyCust = random_int(0, $count);
            $memberId = $custExist[$keyCust];
            $keyParent = random_int(0, $count);
            $parentIdNew = $custExist[$keyParent];
            if ($parentIdNew != $memberId) {
                $reqChange = new \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\ChangeParent\Request();
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
                $custIdDel = $custExist[$keyDel];
                $reqDel = new \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Delete\Request();
                $reqDel->customerId = $custIdDel;
                $seconds = random_int(0, 3600);
                $date->modify("+$seconds seconds");
                $reqDel->date = $date;
                echo "\ndelete: $custIdDel.";
                $respDel = $srvDwnDelete->exec($reqDel);
                /* remove key from existing customers registry */
                unset($custExist[$keyDel]);
                $custExist = array_values($custExist);
            }

        }
    }


    $em->flush();


    echo "\nDone.";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
