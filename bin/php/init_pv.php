<?php
/**
 * Executable script to add PV movements for development.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';

use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree as ETree;

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
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    /** @var \Praxigento\Milc\Bonus\Api\Service\Bonus\Pv\Register $srvRegister */
    $srvRegister = $container->get(\Praxigento\Milc\Bonus\Api\Service\Bonus\Pv\Register::class);

    /**
     * Load not deleted customers.
     */
    $custs = loadCustomers($em);

    /**
     * Create PV movements.
     */
    $date = new \DateTime();
    foreach ($custs as $cust) {
        $totalAmnt = 0;
        $custId = $cust->member_ref;
        $count = random_int(0, 3);
        /* add from 0 to 3 positive PV movements */
        for ($i = 0; $i < $count; $i++) {
            $amount = random_int(100, 20000) / 100;
            $totalAmnt += $amount;
            $req = new \Praxigento\Milc\Bonus\Api\Service\Bonus\Pv\Register\Request();
            $req->customerId = $custId;
            $req->volume = $amount;
            $isAutoship = (random_int(1, 5) == 1); // 20% - is autoship
            if ($isAutoship)
                $req->isAutoship = true;
            $rsp = $srvRegister->exec($req);
        }
        /* 10% to add negative PV movements */
        if ($totalAmnt > 0) {
            $needNegative = (random_int(1, 10) == 1); // 10% - negative movement
            if ($needNegative) {
                $amount = random_int(100, $totalAmnt * 100) / 100;
                $req = new \Praxigento\Milc\Bonus\Api\Service\Bonus\Pv\Register\Request();
                $req->customerId = $custId;
                $req->volume = (-1) * $amount;
                $rsp = $srvRegister->exec($req);
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
 * Load all customers and create map by ID.
 *
 * @param \Doctrine\ORM\EntityManagerInterface $em
 * @return ETree[]
 */
function loadCustomers(\Doctrine\ORM\EntityManagerInterface $em)
{
    $result = [];
    $as = 'tree';
    $qb = $em->createQueryBuilder();
    $qb = $qb->select($as);
    $qb->from(ETree::class, $as);
    $query = $qb->getQuery();
    $all = $query->getArrayResult();
    foreach ($all as $one) {
        $item = new ETree($one);
        $result[$item->member_ref] = $item;
    }
    return $result;
}