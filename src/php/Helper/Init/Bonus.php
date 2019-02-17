<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper\Init;


use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan as EPlan;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type as ECalcType;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type\Deps\After as ECalcTypeDepsOn;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Rank as EPlanRank;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as ESuite;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc as ESuiteCalc;

class Bonus
    implements \Praxigento\Milc\Bonus\Api\Helper\Init\Bonus
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format */
    private $hlpFormat;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Api\Helper\Format $hlpFormat
    ) {
        $this->dao = $dao;
        $this->hlpFormat = $hlpFormat;
    }

    public function calcTypes()
    {
        $result = [];
        //
        $code = Cfg::CALC_TYPE_COLLECT_CV;
        $id = $this->calcTypesAdd($code, 'CV collection.');
        $result[$code] = $id;
        //
        $code = Cfg::CALC_TYPE_TREE_PLAIN;
        $deps = [$id];
        $id = $this->calcTypesAdd($code, 'Plain downline tree composition.', $deps);
        $result[$code] = $id;
        //
        $code = Cfg::CALC_TYPE_QUALIFY_RANK_SIMPLE;
        $deps = [$id];
        $id = $this->calcTypesAdd($code, 'Simple qualification calculation (based on PV, ...).', $deps);
        $result[$code] = $id;
        //
        $code = Cfg::CALC_TYPE_BONUS_LEVEL_BASED;
        $deps = [$id];
        $id = $this->calcTypesAdd($code, 'Level based bonus calculation.', $deps);
        $result[$code] = $id;
        return $result;
    }

    private function calcTypesAdd($code, $note, $depsOn = [])
    {
        $key = [ECalcType::CODE => $code];
        /** @var ECalcType $found */
        $found = $this->dao->getOne(ECalcType::class, $key);
        if (!$found) {
            $calcType = new ECalcType();
            $calcType->code = $code;
            $calcType->note = $note;
            $result = $this->dao->create($calcType);
            foreach ($depsOn as $one) {
                $link = new ECalcTypeDepsOn();
                $link->ref = $result;
                $link->other_ref = $one;
                $this->dao->create($link);
            }
        } else {
            $result = $found->id;
        }
        return $result;
    }

    public function plan(): EPlan
    {
        $note = 'Development plan.';
        $result = $this->dao->getOne(EPlan::class, [EPlan::NOTE => $note]);
        if (!$result) {
            $plan = new EPlan();
            $plan->date_created = $this->hlpFormat->getDateNowUtc();
            $plan->note = $note;
            $id = $this->dao->create($plan);
            $result = $this->dao->getOne(EPlan::class, [EPlan::ID => $id]);
        }
        return $result;
    }

    public function planRanks($planId)
    {
        $humanId = $this->planRanksInitOne(Cfg::RANK_HUMAN, 'Human (lowest)', 1, $planId);
        $heroId = $this->planRanksInitOne(Cfg::RANK_HERO, 'Hero', 2, $planId);
        $angelId = $this->planRanksInitOne(Cfg::RANK_ANGEL, 'Angel', 3, $planId);
        $godId = $this->planRanksInitOne(Cfg::RANK_GOD, 'God (highest)', 4, $planId);
        return [$humanId, $heroId, $angelId, $godId];
    }

    private function planRanksInitOne($code, $note, $sequence, $planId)
    {
        $key = [EPlanRank::CODE => $code];
        /** @var EPlanRank $found */
        $found = $this->dao->getOne(EPlanRank::class, $key);
        if (!$found) {
            $entity = new EPlanRank();
            $entity->plan_ref = $planId;
            $entity->code = $code;
            $entity->note = $note;
            $entity->sequence = $sequence;
            $result = $this->dao->create($entity);
        } else {
            $result = $found->id;
        }
        return $result;
    }

    public function suite($planId): ESuite
    {
        $key = [ESuite::PLAN_REF => $planId];
        $result = $this->dao->getOne(ESuite::class, $key);
        if (!$result) {
            $suite = new ESuite();
            $suite->plan_ref = $planId;
            $suite->date_created = $this->hlpFormat->getDateNowUtc();
            $suite->period = Cfg::BONUS_PERIOD_TYPE_MONTH;
            $suite->note = 'Dev. suite (monthly based).';
            $this->dao->create($suite);
            $result = $this->dao->getOne(ESuite::class, $key);
        }
        return $result;
    }

    public function suiteCalcs($suiteId, $typeIds)
    {
        $result = [];
        $key = [ESuiteCalc::SUITE_REF => $suiteId];
        /** @var ESuiteCalc[] $found */
        $found = $this->dao->getSet(ESuiteCalc::class, $key);
        if (!count($found)) {
            $i = 1;
            foreach ($typeIds as $typeCode => $typeId) {
                $calc = new ESuiteCalc();
                $calc->suite_ref = $suiteId;
                $calc->type_ref = $typeId;
                $calc->date_created = new \DateTime();
                $calc->sequence = $i++;
                $id = $this->dao->create($calc);
                $result[$typeCode] = $id;
            }
        } else {
            $flipped = array_flip($typeIds);
            foreach ($found as $one) {
                $typeId = $one->type_ref;
                $code = $flipped[$typeId];
                $id = $one->id;
                $result[$code] = $id;
            }
        }
        return $result;
    }
}