<?php
/**
 * Executable script to emulate Unilevel bonus calculation.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'commons.php';

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry as EBonCvReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type as EBonCalcType;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as EBonSuite;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc as EBonSuiteCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Cv as EBonCvColect;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Period as EBonPeriod;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race as EBonRace;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race\Calc as EBonPeriodCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Tree as EPeriodTree;
use Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Request as ATreeGetRequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Response as ATreeGetResponse;

/**
 * Get DI container then populate database schema with DEM'ed entities.
 */
try {
    /**
     * Setup IoC-container.
     */
    $app = \Praxigento\Milc\Bonus\App::getInstance();
    $container = $app->getContainer();

    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $em->beginTransaction();

    /* Create/get first period for the first suite */
    /** @var EBonPeriod $period */
    $period = calc_bonus_register_period($container);
    $periodId = $period->id;
    $suiteId = $period->suite_ref;
    /* create/get calculation race for the period */
    $race = calc_bonus_register_race($container, $periodId);
    $raceId = $race->id;
    /**
     * STEP 1: Collect CV for period.
     */
    $typeCode = Cfg::CALC_TYPE_COLLECT_CV;
    $calc = calc_bonus_get_calc_by_type($container, $suiteId, $typeCode, 1);
    /* get period race related calc instance (CV collection) */
    $calcInst = calc_bonus_get_calc_instance($container, $raceId, $calc->id);
    /* collect CV for the period */
    $collected = calc_cv_collect($container, $calcInst->id, $period->date_begin);
    /**
     * STEP 2: Compose tree (just copy plain tree for the end of the period).
     */
    $typeCode = Cfg::CALC_TYPE_TREE_PLAIN;
    $calc = calc_bonus_get_calc_by_type($container, $suiteId, $typeCode, 2);
    $calcInst = calc_bonus_get_calc_instance($container, $raceId, $calc->id);
    $calcTreeId = $calcInst->id;
    $tree = calc_tree_plain($container, $period, $calcInst->id, $collected);
    /**
     * Step 3: Qualification.
     */
    $typeCode = Cfg::CALC_TYPE_QUALIFY_RANK_SIMPLE;
    $calc = calc_bonus_get_calc_by_type($container, $suiteId, $typeCode, 3);
    $calcInst = calc_bonus_get_calc_instance($container, $raceId, $calc->id);
    $calcRanksId = $calcInst->id;
    calc_qual_save_ranks($container, $calcInst->id, $tree);
    /**
     * Step 4: Level Based Commissions.
     */
    $typeCode = Cfg::CALC_TYPE_COMM_LEVEL_BASED;
    $calc = calc_bonus_get_calc_by_type($container, $suiteId, $typeCode, 4);
    $calcInst = calc_bonus_get_calc_instance($container, $raceId, $calc->id);
    $calcCommId = $calcInst->id;
    calc_bonus_comm_level_based($container, $calcCommId, $calcTreeId, $calcRanksId);

    $em->commit();

    echo "\nDone.\n";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $calcThis
 * @param int $calcTree
 * @param int $calcRanks
 */
function calc_bonus_comm_level_based($container, $calcThis, $calcTree, $calcRanks)
{
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased $srv */
    $srv = $container->get(\Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased::class);
    $req = new \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Request();
    $req->thisCalcInstId = $calcThis;
    $req->treeCalcInstId = $calcTree;
    $req->ranksCalcInstId = $calcRanks;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Response $resp */
    $resp = $srv->exec($req);
    /* save commissions */
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $entries = $resp->commissions;
    foreach ($entries as $entry) {
        $em->persist($entry);
    }
    $em->flush();
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return EBonPeriod
 */
function calc_bonus_register_period($container)
{
    $found = common_get_by_attr($container, EBonSuite::class, [EBonSuite::NOTE => Cfg::SUITE_NOTE]);
    $data = reset($found);
    $suite = new EBonSuite($data);

    $found = common_get_by_attr($container, EBonPeriod::class, [EBonPeriod::SUITE_REF => $suite->id]);
    if (!$found) {
        $periodBegin = \DateTime::createFromFormat(Cfg::BEGINNING_OF_AGES_FORMAT, Cfg::BEGINNING_OF_AGES);
        $periodBegin->modify('first day of');
        $result = new EBonPeriod();
        $result->suite_ref = $suite->id;
        $result->date_begin = $periodBegin;
        $result->state = Cfg::BONUS_PERIOD_STATE_OPEN;
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($result);
        $em->flush();
    } else {
        $data = reset($found);
        $result = new EBonPeriod($data);
    }
    return $result;
}

/**
 * Create/get period race for the given period.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param int $periodId
 * @return EBonRace
 */
function calc_bonus_register_race($container, $periodId)
{
    $found = common_get_by_attr($container, EBonRace::class, [EBonRace::PERIOD_REF => $periodId]);
    if (!$found) {
        $dateStarted = new \DateTime();
        $result = new EBonRace();
        $result->period_ref = $periodId;
        $result->date_started = $dateStarted;
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($result);
        $em->flush();
    } else {
        $data = reset($found);
        $result = new EBonRace($data);
    }
    return $result;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $suiteId
 * @param string $typeCode
 * @param int $sequence
 * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc
 * @throws \Exception
 */
function calc_bonus_get_calc_by_type($container, $suiteId, $typeCode, $sequence)
{
    $found = common_get_by_attr($container, EBonCalcType::class, [EBonCalcType::CODE => $typeCode]);
    $data = reset($found);
    $type = new EBonCalcType($data);
    $typeId = $type->id;
    $bind = [
        EBonSuiteCalc::SUITE_REF => $suiteId,
        EBonSuiteCalc::TYPE_REF => $typeId
    ];
    $found = common_get_by_attr($container, EBonSuiteCalc::class, $bind);
    if (!$found) {
        $result = new EBonSuiteCalc();
        $result->suite_ref = $suiteId;
        $result->type_ref = $typeId;
        $result->sequence = $sequence;
        $result->date_created = new \DateTime();
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($result);
        $em->flush();
    } else {
        $data = reset($found);
        $result = new EBonSuiteCalc($data);
    }
    return $result;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $raceId
 * @param int $calcId
 * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race\Calc
 */
function calc_bonus_get_calc_instance($container, $raceId, $calcId)
{
    $bind = [
        EBonPeriodCalc::RACE_REF => $raceId,
        EBonPeriodCalc::CALC_REF => $calcId
    ];
    $found = common_get_by_attr($container, EBonPeriodCalc::class, $bind);
    if (!$found) {
        $result = new EBonPeriodCalc();
        $result->race_ref = $raceId;
        $result->calc_ref = $calcId;
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($result);
        $em->flush();
    } else {
        $data = reset($found);
        $result = new EBonPeriodCalc($data);
    }
    return $result;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $calcInstId
 * @param \DateTime $datePeriod
 * @return EBonCvColect[]
 */
function calc_cv_collect($container, $calcInstId, $datePeriod)
{
    $result = [];
    $bind = [
        EBonCvColect::CALC_INST_REF => $calcInstId
    ];
    $found = common_get_by_attr($container, EBonCvColect::class, $bind);
    if (!$found) {
        /**
         * Collect CV movements for period and save collected data.
         */
        $all = calc_cv_collect_get_movements($container, $datePeriod);
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        foreach ($all as $data) {
            $entry = new EBonCvColect($data);
            $entry->calc_inst_ref = $calcInstId;
            $em->persist($entry);
            $em->flush();
            $result[] = $entry;
        }
    } else {
        /* load collected data */
        foreach ($found as $data) {
            $entry = new EBonCvColect($data);
            $result[] = $entry;
        }
    }
    return $result;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param $datePeriod
 * @return array
 */
function calc_cv_collect_get_movements($container, $datePeriod)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    /** @var \Doctrine\ORM\QueryBuilder $qb */
    $qb = $em->createQueryBuilder();
    $as = 'main';
    $cols = [
        EBonCvColect::CLIENT_REF => "$as." . EBonCvReg::CLIENT_REF,
        EBonCvColect::IS_AUTOSHIP => "$as." . EBonCvReg::IS_AUTOSHIP,
        "SUM($as." . EBonCvReg::VOLUME . ") as " . EBonCvColect::VOLUME
    ];
    $qb->select($cols);
    $qb->from(EBonCvReg::class, $as);
    /* prepare working vars */
    $dateFrom = clone $datePeriod;
    $dateTo = clone $dateFrom;
    $dateFrom->modify('midnight');
    $dateTo->modify('+1 month');
    /* add WHERE clauses */
    $name = EBonCvReg::DATE;
    $bndFrom = 'from';
    $qb->andWhere("$as.$name>=:$bndFrom");
    $bndTo = 'to';
    $qb->andWhere("$as.$name<:$bndTo");
    $params = [
        $bndFrom => $dateFrom,
        $bndTo => $dateTo
    ];
    $qb->setParameters($params);
    $qb->groupBy("$as." . EBonCvReg::CLIENT_REF, "$as." . EBonCvReg::IS_AUTOSHIP);
    $query = $qb->getQuery();
    /* create collected items and compose result */
    $result = $query->getArrayResult();
    return $result;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $calcInstId
 * @param EPeriodTree[] $tree
 */
function calc_qual_save_ranks($container, $calcInstId, $tree)
{

    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple $srvProc */
    $srvProc = $container->get(\Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple::class);
    $req = new \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Request();
    $req->calcInstId = $calcInstId;
    $req->tree = $tree;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Response $resp */
    $resp = $srvProc->exec($req);
    $entries = $resp->entries;
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    foreach ($entries as $entry) {
        $em->persist($entry);
    }
    $em->flush();
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result $period
 * @param int $calcInstId
 * @param EBonCvColect[] $collected
 */
function calc_tree_plain($container, EBonPeriod $period, $calcInstId, $collected)
{
    $result = [];
    $lastDate = clone $period->date_begin;
    $lastDate->modify("last day of");
    $formatted = $lastDate->format("Y-m-d");
    /* map CV by client/autoship */
    $mapById = [];
    foreach ($collected as $item) {
        $clientId = $item->client_ref;
        $isAutoship = (bool)$item->is_autoship;
        $mapById[$clientId][$isAutoship] = $item->volume;
    }

    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get $srv */
    $srv = $container->get(\Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get::class);
    $req = new ATreeGetRequest();
    $req->date = $formatted;
    /** @var ATreeGetResponse $resp */
    $resp = $srv->exec($req);
    $entries = $resp->entries;
    if (is_array($entries)) {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        foreach ($entries as $entry) {
            $ownPv = (isset($mapById[$entry->client_id][false])) ? $mapById[$entry->client_id][false] : 0;
            $apv = (isset($mapById[$entry->client_id][true])) ? $mapById[$entry->client_id][true] : 0;
            $item = new EPeriodTree();
            $item->calc_inst_ref = $calcInstId;
            $item->client_ref = $entry->client_id;
            $item->parent_ref = $entry->parent_id;
            $item->apv = $apv;
            $item->pv = ($apv + $ownPv);
            $em->persist($item);
            $result[] = $item;
        }
        $em->flush();
    }
    return $result;
}