<?php
/**
 * Executable script to create downline related data for retrospective queries.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';

use Doctrine\ORM\EntityManager as DocEnMgr;
use Doctrine\ORM\Tools\Setup as DocSetup;
use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree\Trace as ETreeTrace;
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
     * Init Doctrine ORM.
     */
    $isDevMode = true;
    $config = DocSetup::createAnnotationMetadataConfiguration(array(__DIR__ . "/../../src/php"), $isDevMode);
    $cfg = $container->get(\TeqFw\Lib\Db\Api\Data\Cfg\Db::class);
    $connectionParams = (array)$cfg;
    $em = DocEnMgr::create($connectionParams, $config);

    /**
     * Create 1000 users.
     */
    $date = new \DateTime();
    $date->modify('-100 days');

    for ($i = 0; $i < 1000; $i++) {
        /* create new customer */
        $partner = new EResPartner();
        $em->persist($partner);
        $em->flush($partner);
        /* register new customer in trace log */
        $trace = new ETreeTrace();
        $trace->member_ref = $partner->id;
        if ($i == 0) {
            $trace->parent_ref = $partner->id;
        } else {
            $trace->parent_ref = random_int(1, $i);
        }
        $trace->date = $date;
        $seconds = random_int(0, 3600);
        $date->modify("+$seconds seconds");
        $em->persist($trace);
        /* register new customer in the tree */
        $tree = new ETree();
        $tree->member_ref = $trace->member_ref;
        $tree->parent_ref = $trace->parent_ref;
        $tree->depth = 1;
        $tree->path = '::';
        $tree->mlm_id = $trace->member_ref;
        $em->persist($tree);

        /**
         * Random update for existing customer.
         */
        if ($i > 1) {
            $memberId = random_int(1, $i);
            /** @var ETree $entry */
            $entry = $em->find(ETree::class, $memberId);
            $parentIdNew = random_int(1, $i);
            if ($parentIdNew != $entry->parent_ref) {
                $trace = new ETreeTrace();
                $trace->member_ref = $entry->member_ref;
                $trace->parent_ref = $parentIdNew;
                $seconds = random_int(0, 3600);
                $date->modify("+$seconds seconds");
                $trace->date = $date;
                $em->persist($trace);
                $entry->parent_ref = $parentIdNew;
                $em->persist($entry);
            }
        }
    }


    $em->flush();


    echo "\nDone.";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}