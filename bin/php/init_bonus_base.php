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
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Calc\Type as EBonBaseCalcType;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Plan as EBonBasePlan;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Plan\Calc as EBonBasePlanCalc;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Plan\Rank as EBonBasePlanRank;

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


    echo "\nDone.";
} catch (\Throwable $e) {
    /** catch all exceptions and just print out the message */
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

function init_bonus_plan($container)
{
    $plan = new EBonBasePlan();
    $plan->period = Cfg::BONUS_PERIOD_TYPE_MONTH;
    $plan->note = 'Simple Unilevel plan for development.';
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $em->persist($plan);
    $em->flush();
    return $plan->id;
}

function init_bonus_plan_ranks($container, $planId)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    /* Human */
    $human = new EBonBasePlanRank();
    $human->plan_ref = $planId;
    $human->code = Cfg::RANK_HUMAN;
    $human->note = 'Human (lowest)';
    $human->sequence = 1;
    $em->persist($human);
    /* Hero */
    $hero = new EBonBasePlanRank();
    $hero->plan_ref = $planId;
    $hero->code = Cfg::RANK_HERO;
    $hero->note = 'Hero';
    $hero->sequence = 2;
    $em->persist($hero);
    /* Angel */
    $angel = new EBonBasePlanRank();
    $angel->plan_ref = $planId;
    $angel->code = Cfg::RANK_ANGEL;
    $angel->note = 'Angel';
    $angel->sequence = 3;
    $em->persist($angel);
    /* God */
    $god = new EBonBasePlanRank();
    $god->plan_ref = $planId;
    $god->code = Cfg::RANK_GOD;
    $god->note = 'God (highest)';
    $god->sequence = 4;
    $em->persist($god);
    /**/
    $em->flush();
    return [$human->rank_id, $hero->rank_id, $angel->rank_id, $god->rank_id];
}

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return int
 */
function init_bonus_calc_type($container)
{
    $calcType = new EBonBaseCalcType();
    $calcType->code = 'LEVEL_BASED';
    $calcType->note = 'Levels Based Commissions';
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $em->persist($calcType);
    $em->flush();
    return $calcType->id;
}

function init_bonus_plan_calcs($container, $planId, $typeId)
{
    $calc = new EBonBasePlanCalc();
    $calc->plan_ref = $planId;
    $calc->type_ref = $typeId;
    $calc->date_started = new \DateTime();
    $calc->sequence = 1;
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $em->persist($calc);
    $em->flush();
}
