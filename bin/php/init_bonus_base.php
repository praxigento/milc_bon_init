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
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rank as EPlanQual;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule as EQualRule;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Group as EQualRuleGroup;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Group\Ref as EQualRuleGroupRef;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Pv as EQualRulePv;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Rank as EQualRuleRank;
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

    /** @var \Praxigento\Milc\Bonus\Helper\Init\Bonus $hlpInit */
    $hlpInit = $container->get(\Praxigento\Milc\Bonus\Helper\Init\Bonus::class);
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $em->beginTransaction();

    $plan = $hlpInit->plan();
    $planId = $plan->id;
    $suite = $hlpInit->suite($planId);
    $suiteId = $suite->id;
    $ranks = $hlpInit->planRanks($planId);
    $typeIds = $hlpInit->calcTypes();
    $calcIds = $hlpInit->suiteCalcs($suiteId, $typeIds);
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

