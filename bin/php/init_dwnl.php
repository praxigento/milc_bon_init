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
        $parentId = ($i == 0) ? $customerId : random_int(1, $i);
        $mlmId = $customerId;

        /* register new customer in downline */
        $reqAdd = new \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Add\Request();
        $reqAdd->customerId = $customerId;
        $reqAdd->parentId = $parentId;
        $reqAdd->mlmId = $mlmId;
        $seconds = random_int(0, 3600);
        $date->modify("+$seconds seconds");
        $reqAdd->date = $date;
        $respAdd = $srvDwnCustAdd->exec($reqAdd);

        $count = count($custExist) - 1;
        if ($count > 1) {
            /**
             * Random parent update for existing customer.
             */
            $keyCust = random_int(1, $count);
            $memberId = $custExist[$keyCust];
            $keyParent = random_int(1, $count);
            $parentIdNew = $custExist[$keyParent];
            $reqChange = new \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\ChangeParent\Request();
            $reqChange->customerId = $memberId;
            $reqChange->parentIdNew = $parentIdNew;
            $seconds = random_int(0, 3600);
            $date->modify("+$seconds seconds");
            $reqChange->date = $date;
            $respChange = $srvDwnChangeParent->exec($reqChange);
            /* delete customer with 25%*/
            if (random_int(1, 4) == 2) {
                $keyDel = random_int(1, $count);
                $custIdDel = $custExist[$keyDel];
                $reqDel = new \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Delete\Request();
                $reqDel->customerId = $custIdDel;
                $seconds = random_int(0, 3600);
                $date->modify("+$seconds seconds");
                $reqDel->date = $date;
                $respDel = $srvDwnDelete->exec($reqDel);
            }

        }
    }


    $em->flush();


    echo "\nDone.";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
