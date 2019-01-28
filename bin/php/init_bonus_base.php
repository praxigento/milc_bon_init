<?php
/**
 * Executable script to init base configuration for bonuses.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */
/* PHP Composer's autoloader (access to dependencies sources) */
require_once __DIR__ . '/../../vendor/autoload.php';

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Calc\Type as ECalcType;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Plan as EPlan;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Plan\Calc as EPlanCalc;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Plan\Qualification as EPlanQual;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Plan\Rank as EPlanRank;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Qualification\Rule as EQualRule;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Qualification\Rule\Group as EQualRuleGroup;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Qualification\Rule\Group\Ref as EQualRuleGroupRef;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Qualification\Rule\Pv as EQualRulePv;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Qualification\Rule\Rank as EQualRuleRank;

/**
 * Get DI container then populate database schema with DEM'ed entities.
 */
try {
    /**
     * Setup IoC-container.
     */
    $app = \Praxigento\Milc\Bonus\App::getInstance();
    $container = $app->getContainer();

    $planId = init_bonus_plan($container);
    $ranks = init_bonus_plan_ranks($container, $planId);
    $calcTypeId = init_bonus_calc_type($container);
    $calcId = init_bonus_plan_calcs($container, $planId, $calcTypeId);

    init_bonus_qual_rules($container, $calcId, $ranks);

    echo "\nDone.";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return int
 */
function init_bonus_plan($container)
{
    $plan = new EPlan();
    $plan->period = Cfg::BONUS_PERIOD_TYPE_MONTH;
    $plan->note = 'Simple Unilevel plan for development.';
    $found = getByAttribute($container, EPlan::class, EPlan::NOTE, $plan->note);
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
    $found = getByAttribute($container, EPlanRank::class, EPlanRank::CODE, $human->code);
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
    $found = getByAttribute($container, EPlanRank::class, EPlanRank::CODE, $hero->code);
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
    $found = getByAttribute($container, EPlanRank::class, EPlanRank::CODE, $angel->code);
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
    $found = getByAttribute($container, EPlanRank::class, EPlanRank::CODE, $god->code);
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
    $calcType = new ECalcType();
    $calcType->code = 'LEVEL_BASED';
    $calcType->note = 'Levels Based Commissions';
    $found = getByAttribute($container, ECalcType::class, ECalcType::CODE, $calcType->code);
    if (!$found) {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($calcType);
        $em->flush();
    } else {
        $data = reset($found);
        $calcType = new ECalcType($data);
    }
    return $calcType->id;
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @param int $planId
 * @param int $typeId
 * @return int
 * @throws \Exception
 */
function init_bonus_plan_calcs($container, $planId, $typeId)
{
    $calc = new EPlanCalc();
    $calc->plan_ref = $planId;
    $calc->type_ref = $typeId;
    $calc->date_started = new \DateTime();
    $calc->sequence = 1;
    $found = getByAttribute($container, EPlanCalc::class, EPlanCalc::PLAN_REF, $calc->plan_ref);
    if (!$found) {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($calc);
        $em->flush();
    } else {
        $data = reset($found);
        $calc = new EPlanCalc($data);
    }
    return $calc->id;
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
    $rulePv = init_bonus_qual_rules_create_pv($container, 35, 0, false);
    $ruleApv = init_bonus_qual_rules_create_pv($container, 25, 1, true);
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
    $rulePv = init_bonus_qual_rules_create_pv($container, 70, 0, false);
    $ruleApv = init_bonus_qual_rules_create_pv($container, 50, 1, true);
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
    $rulePv = init_bonus_qual_rules_create_pv($container, 140, 0, false);
    $ruleApv = init_bonus_qual_rules_create_pv($container, 100, 1, true);
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
    $rulePv = init_bonus_qual_rules_create_pv($container, 280, 0, false);
    $ruleApv = init_bonus_qual_rules_create_pv($container, 200, 1, true);
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
 * @return \Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Qualification\Rule\Pv
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
 * @return \Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Qualification\Rule\Rank
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

/**
 * Find entities by attribute value.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param string $class
 * @param string $attrName
 * @param string $attrValue
 * @return array
 */
function getByAttribute($container, $class, $attrName, $attrValue)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    /** @var \Doctrine\ORM\QueryBuilder $qb */
    $qb = $em->createQueryBuilder();
    $as = 'main';
    $qb->select($as);
    $qb->from($class, $as);
    $qb->andWhere("$as.$attrName=:param");
    $qb->setParameters(['param' => $attrValue]);
    $query = $qb->getQuery();
    $result = $query->getArrayResult();
    return $result;
}