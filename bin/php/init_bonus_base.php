<?php
/**
 * Executable script to init base configuration for bonuses.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'commons.php';

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Comm\Level as ECalcLevel;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule as EQualRule;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Group as EQualRuleGroup;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Group\Ref as EQualRuleGroupRef;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Pv as EQualRulePv;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Rank as EQualRuleRank;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan as EPlan;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type as ECalcType;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type\Deps\After as ECalcTypeDepsOn;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rank as EPlanQual;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Rank as EPlanRank;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as ESuite;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc as ESuiteCalc;

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

    $planId = init_bonus_plan($container);
    $suiteId = init_bonus_suite($container, $planId);
    $ranks = init_bonus_plan_ranks($container, $planId);
    $typeIds = init_bonus_calc_type($container);
    $calcIds = init_bonus_suite_calcs($container, $suiteId, $typeIds);
    $calcIdQual = $calcIds[Cfg::CALC_TYPE_QUALIFY_RANK_SIMPLE];
    init_bonus_qual_rules($container, $calcIdQual, $ranks);
    $calcIdLevels = $calcIds[Cfg::CALC_TYPE_BONUS_LEVEL_BASED];
    init_bonus_levels($container, $calcIdLevels, $ranks);

    $em->commit();

    echo "\nDone.\n";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $calcId
 * @param int[] $ranks
 */
function init_bonus_levels($container, $calcId, $ranks)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $cfg = [
        [1 => 0.20],    // HUM
        [1 => 0.20, 2 => 0.15], // HER
        [1 => 0.20, 2 => 0.15, 3 => 0.10], // ANG
        [1 => 0.20, 2 => 0.15, 3 => 0.10, 4 => 0.05] //GOD
    ];
    $i = 0;
    foreach ($ranks as $rankId) {
        $levels = $cfg[$i++];
        foreach ($levels as $level => $percent) {
            $entity = new ECalcLevel();
            $entity->calc_ref = $calcId;
            $entity->rank_ref = $rankId;
            $entity->level = $level;
            $entity->percent = $percent;
            $em->persist($entity);
        }
    }
    $em->flush();
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return int
 */
function init_bonus_plan($container)
{
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format $format */
    $format = $container->get(\Praxigento\Milc\Bonus\Api\Helper\Format::class);
    $plan = new EPlan();
    $plan->date_created = $format->getDateNowUtc();

    $plan->note = 'Development plan.';
    $found = common_get_by_attr($container, EPlan::class, [EPlan::NOTE => $plan->note]);
    if (!$found) {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($plan);
        $em->flush();
    } else {
        $data = reset($found);
        $plan = new EPlan($data);
    }
    return $plan->id;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $planId
 * @return int
 */
function init_bonus_suite($container, $planId)
{
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format $format */
    $format = $container->get(\Praxigento\Milc\Bonus\Api\Helper\Format::class);
    $suite = new ESuite();
    $suite->plan_ref = $planId;
    $suite->date_created = $format->getDateNowUtc();
    $suite->period = Cfg::BONUS_PERIOD_TYPE_MONTH;

    $suite->note = Cfg::SUITE_NOTE;
    $found = common_get_by_attr($container, ESuite::class, [ESuite::PLAN_REF => $planId]);
    if (!$found) {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($suite);
        $em->flush();
    } else {
        $data = reset($found);
        $suite = new EPlan($data);
    }
    return $suite->id;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $planId
 * @return array
 */
function init_bonus_plan_ranks($container, $planId)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    /* Human */
    $human = new EPlanRank();
    $human->plan_ref = $planId;
    $human->code = Cfg::RANK_HUMAN;
    $human->note = 'Human (lowest)';
    $human->sequence = 1;
    $found = common_get_by_attr($container, EPlanRank::class, [EPlanRank::CODE => $human->code]);
    if (!$found) {
        $em->persist($human);
    } else {
        $data = reset($found);
        $human = new EPlanRank($data);
    }
    /* Hero */
    $hero = new EPlanRank();
    $hero->plan_ref = $planId;
    $hero->code = Cfg::RANK_HERO;
    $hero->note = 'Hero';
    $hero->sequence = 2;
    $found = common_get_by_attr($container, EPlanRank::class, [EPlanRank::CODE => $hero->code]);
    if (!$found) {
        $em->persist($hero);
    } else {
        $data = reset($found);
        $hero = new EPlanRank($data);
    }
    /* Angel */
    $angel = new EPlanRank();
    $angel->plan_ref = $planId;
    $angel->code = Cfg::RANK_ANGEL;
    $angel->note = 'Angel';
    $angel->sequence = 3;
    $found = common_get_by_attr($container, EPlanRank::class, [EPlanRank::CODE => $angel->code]);
    if (!$found) {
        $em->persist($angel);
    } else {
        $data = reset($found);
        $angel = new EPlanRank($data);
    }
    /* God */
    $god = new EPlanRank();
    $god->plan_ref = $planId;
    $god->code = Cfg::RANK_GOD;
    $god->note = 'God (highest)';
    $god->sequence = 4;
    $found = common_get_by_attr($container, EPlanRank::class, [EPlanRank::CODE => $god->code]);
    if (!$found) {
        $em->persist($god);
    } else {
        $data = reset($found);
        $god = new EPlanRank($data);
    }
    /**/
    $em->flush();
    return [$human->id, $hero->id, $angel->id, $god->id];
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return int
 */
function init_bonus_calc_type($container)
{
    $result = [];
    //
    $code = Cfg::CALC_TYPE_COLLECT_CV;
    $id = init_bonus_calc_type_add($container, $code, 'CV collection.');
    $result[$code] = $id;
    //
    $code = Cfg::CALC_TYPE_TREE_PLAIN;
    $deps = [$id];
    $id = init_bonus_calc_type_add($container, $code, 'Based on plain tree.', $deps);
    $result[$code] = $id;
    //
    $code = Cfg::CALC_TYPE_QUALIFY_RANK_SIMPLE;
    $deps = [$id];
    $id = init_bonus_calc_type_add($container, $code, 'Simple qualification calculation (based on PV, ...).', $deps);
    $result[$code] = $id;
    //
    $code = Cfg::CALC_TYPE_BONUS_LEVEL_BASED;
    $deps = [$id];
    $id = init_bonus_calc_type_add($container, $code, 'Level based bonus calculation.', $deps);
    $result[$code] = $id;
    return $result;
}

function init_bonus_calc_type_add($container, $code, $note, $depsOn = [], $depsBefore = [])
{
    $found = common_get_by_attr($container, ECalcType::class, [ECalcType::CODE => $code]);
    if (!$found) {
        $calcType = new ECalcType();
        $calcType->code = $code;
        $calcType->note = $note;
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($calcType);
        $em->flush();
        foreach ($depsOn as $one) {
            $link = new ECalcTypeDepsOn();
            $link->ref = $calcType->id;
            $link->other_ref = $one;
            $em->persist($link);
            $em->flush();
        }
    } else {
        $data = reset($found);
        $calcType = new ECalcType($data);
    }
    return $calcType->id;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $suiteId
 * @param int[] $typeIds
 * @return int
 * @throws \Exception
 */
function init_bonus_suite_calcs($container, $suiteId, $typeIds)
{
    $result = [];
    $found = common_get_by_attr($container, ESuiteCalc::class, [ESuiteCalc::SUITE_REF => $suiteId]);
    if (!$found) {
        $i = 1;
        foreach ($typeIds as $typeCode => $typeId) {
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            /** @var \Doctrine\ORM\EntityManagerInterface $em */
            $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
            $em->persist($calc);
            $em->flush();
            $result[$typeCode] = $calc->id;
        }
    } else {
        $data = reset($found);
        $calc = new ESuiteCalc($data);
    }
    return $result;
}

/**
 * Create root rules for rank qualifications.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param int $calcId
 * @param int[] $ranks
 */
function init_bonus_qual_rules($container, $calcId, $ranks)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);

    $ruleIdHuman = init_bonus_qual_rules_create_rank_human($container);
    $qual = new EPlanQual();
    $qual->calc_ref = $calcId;
    $qual->rank_ref = $ranks[0];
    $qual->rule_ref = $ruleIdHuman;
    $em->persist($qual);

    $ruleIdHero = init_bonus_qual_rules_create_rank_hero($container);
    $qual = new EPlanQual();
    $qual->calc_ref = $calcId;
    $qual->rank_ref = $ranks[1];
    $qual->rule_ref = $ruleIdHero;
    $em->persist($qual);

    $ruleIdAngel = init_bonus_qual_rules_create_rank_angel($container, $ranks);
    $qual = new EPlanQual();
    $qual->calc_ref = $calcId;
    $qual->rank_ref = $ranks[2];
    $qual->rule_ref = $ruleIdAngel;
    $em->persist($qual);

    $ruleIdGod = init_bonus_qual_rules_create_rank_god($container, $ranks);
    $qual = new EPlanQual();
    $qual->calc_ref = $calcId;
    $qual->rank_ref = $ranks[3];
    $qual->rule_ref = $ruleIdGod;
    $em->persist($qual);

    $em->flush();
}

/**
 * Create qualification rules for rank 'Human'.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @return int
 */
function init_bonus_qual_rules_create_rank_human($container)
{
    $rulePv = init_bonus_qual_rules_create_pv($container, 100, 0, false);
    $ruleApv = init_bonus_qual_rules_create_pv($container, 75, 1, true);
    $ids = [$rulePv->ref, $ruleApv->ref];
    $result = init_bonus_qual_rules_create_group($container, Cfg::RULE_GROUP_LOGIC_OR, $ids);
    return $result;
}

/**
 * Create qualification rules for rank 'Hero'.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @return int
 */
function init_bonus_qual_rules_create_rank_hero($container)
{
    $rulePv = init_bonus_qual_rules_create_pv($container, 200, 0, false);
    $ruleApv = init_bonus_qual_rules_create_pv($container, 150, 1, true);
    $ids = [$rulePv->ref, $ruleApv->ref];
    $result = init_bonus_qual_rules_create_group($container, Cfg::RULE_GROUP_LOGIC_OR, $ids);
    return $result;
}

/**
 * Create qualification rules for rank 'Angel'.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param int[] $ranks
 * @return int
 */
function init_bonus_qual_rules_create_rank_angel($container, $ranks)
{
    $rulePv = init_bonus_qual_rules_create_pv($container, 400, 0, false);
    $ruleApv = init_bonus_qual_rules_create_pv($container, 300, 1, true);
    $ids = [$rulePv->ref, $ruleApv->ref];
    $groupPv = init_bonus_qual_rules_create_group($container, Cfg::RULE_GROUP_LOGIC_OR, $ids);
    $rankIdHero = $ranks[1];
    $ruleRank = init_bonus_qual_rules_create_rank($container, $rankIdHero, 3, 0);
    $ids = [$groupPv, $ruleRank->ref];
    $result = init_bonus_qual_rules_create_group($container, Cfg::RULE_GROUP_LOGIC_AND, $ids);
    return $result;
}

/**
 * Create qualification rules for rank 'God'.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param int[] $ranks
 * @return int
 */
function init_bonus_qual_rules_create_rank_god($container, $ranks)
{
    $rulePv = init_bonus_qual_rules_create_pv($container, 800, 0, false);
    $ruleApv = init_bonus_qual_rules_create_pv($container, 600, 1, true);
    $ids = [$rulePv->ref, $ruleApv->ref];
    $groupPv = init_bonus_qual_rules_create_group($container, Cfg::RULE_GROUP_LOGIC_OR, $ids);
    $rankIdHero = $ranks[2];
    $ruleRank = init_bonus_qual_rules_create_rank($container, $rankIdHero, 2, 1);
    $ids = [$groupPv, $ruleRank->ref];
    $result = init_bonus_qual_rules_create_group($container, Cfg::RULE_GROUP_LOGIC_AND, $ids);
    return $result;
}

/**
 * Create one PV rule.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param float $volume
 * @param int $period
 * @param bool $isAutship
 * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Pv
 */
function init_bonus_qual_rules_create_pv($container, $volume, $period, $isAutship = false)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);

    /* register new rule in rule registry */
    $rule = new  EQualRule();
    $rule->type = Cfg::QUAL_RULE_TYPE_PV;
    $em->persist($rule);
    $em->flush();
    $ruleId = $rule->id;

    /* save rule details */
    $result = new EQualRulePv();
    $result->ref = $ruleId;
    $result->volume = $volume;
    $result->autoship_only = $isAutship;
    $result->period = $period;
    $em->persist($result);
    $em->flush();
    return $result;
}

/**
 * Create one Rank rule.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param $rankId
 * @param $count
 * @param $period
 * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Rank
 */
function init_bonus_qual_rules_create_rank($container, $rankId, $count, $period)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);

    /* register new rule in rule registry */
    $rule = new  EQualRule();
    $rule->type = Cfg::QUAL_RULE_TYPE_RANK;
    $em->persist($rule);
    $em->flush();
    $ruleId = $rule->id;

    /* save rule details */
    $result = new EQualRuleRank();
    $result->ref = $ruleId;
    $result->rank_ref = $rankId;
    $result->count = $count;
    $result->period = $period;
    $em->persist($result);
    $em->flush();
    return $result;
}

/**
 * Create grouping rule for set of rules with given IDs.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param string $logic AND, OR, ...
 * @param int[] $otherIds
 * @return int rule ID
 */
function init_bonus_qual_rules_create_group($container, $logic, $otherIds)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);

    /* register new rule in rule registry */
    $root = new  EQualRule();
    $root->type = Cfg::QUAL_RULE_TYPE_GROUP;
    $em->persist($root);
    $em->flush();
    $result = $root->id;

    /* add grouping rule */
    $group = new  EQualRuleGroup();
    $group->ref = $result;
    $group->logic = $logic;
    $em->persist($group);
    $em->flush();

    /*save references to grouping rules */
    foreach ($otherIds as $one) {
        $ref = new  EQualRuleGroupRef();
        $ref->grouping_ref = $result;
        $ref->grouped_ref = $one;
        $em->persist($ref);
        $em->flush();
    }
    return $result;
}

