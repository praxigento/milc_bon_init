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
    $calc->sequence = 1;
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    $em->persist($calc);
    $em->flush();
}
