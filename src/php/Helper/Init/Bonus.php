<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper\Init;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Comm\Level as ECalcLevel;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Rank as EPlanQual;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Rank\Rule as EQualRule;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Rank\Rule\Group as EQualRuleGroup;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Rank\Rule\Group\Ref as EQualRuleGroupRef;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Rank\Rule\Pv as EQualRulePv;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Rank\Rule\Rank as EQualRuleRank;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan as EPlan;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type as ECalcType;
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
        $code = Cfg::CALC_TYPE_COMM_BINARY;
        $idCommLevel = $this->calcTypesAdd($code, 'Binary commission calculation.');
        $result[$code] = $idCommLevel;
        //
        $code = Cfg::CALC_TYPE_COMM_LEVEL_BASED;
        $idCommLevel = $this->calcTypesAdd($code, 'Level based bonus calculation.');
        $result[$code] = $idCommLevel;
        //
        $code = Cfg::CALC_TYPE_COMPRESSION;
        $idDowngrade = $this->calcTypesAdd($code, 'Distributors downgrade calculation.');
        $result[$code] = $idDowngrade;
        //
        $code = Cfg::CALC_TYPE_CV_COLLECT;
        $idCvCollect = $this->calcTypesAdd($code, 'CV collection.');
        $result[$code] = $idCvCollect;
        //
        $code = Cfg::CALC_TYPE_CV_GROUPING_GV;
        $idGroupGv = $this->calcTypesAdd($code, 'GV calculation in the tree.');
        $result[$code] = $idGroupGv;
        //
        $code = Cfg::CALC_TYPE_CV_GROUPING_OV;
        $idGroupOv = $this->calcTypesAdd($code, 'OV calculation in the tree.');
        $result[$code] = $idGroupOv;
        //
        $code = Cfg::CALC_TYPE_CV_GROUPING_PV;
        $idGroupPv = $this->calcTypesAdd($code, 'PV calculation in the tree.');
        $result[$code] = $idGroupPv;
        //
        $code = Cfg::CALC_TYPE_DOWNGRADE;
        $idDowngrade = $this->calcTypesAdd($code, 'Distributors downgrade calculation.');
        $result[$code] = $idDowngrade;
        //
        $code = Cfg::CALC_TYPE_RANK_QUAL;
        $idRank = $this->calcTypesAdd($code, 'Simple qualification calculation (based on PV, ...).');
        $result[$code] = $idRank;
        //
        $code = Cfg::CALC_TYPE_TREE_BINARY;
        $idTreeBinary = $this->calcTypesAdd($code, 'Binary downline tree composition.');
        $result[$code] = $idTreeBinary;
        //
        $code = Cfg::CALC_TYPE_TREE_MATRIX;
        $idTreeMatrix = $this->calcTypesAdd($code, 'Binary downline tree composition.');
        $result[$code] = $idTreeMatrix;
        //
        $code = Cfg::CALC_TYPE_TREE_NATURAL;
        $idTreeNatural = $this->calcTypesAdd($code, 'Plain downline tree composition.');
        $result[$code] = $idTreeNatural;

        return $result;
    }

    /**
     * Get ID for suite calculation by code or create new one and return new ID.
     *
     * @param string $code
     * @param string $note
     * @return int
     */
    private function calcTypesAdd($code, $note)
    {
        $key = [ECalcType::CODE => $code];
        /** @var ECalcType $found */
        $found = $this->dao->getOne(ECalcType::class, $key);
        if (!$found) {
            $calcType = new ECalcType();
            $calcType->code = $code;
            $calcType->note = $note;
            $result = $this->dao->create($calcType);
        } else {
            $result = $found->id;
        }
        return $result;
    }

    public function commLevels($calcId, $ranks)
    {
        $found = $this->dao->getOne(ECalcLevel::class, [ECalcLevel::CALC_REF => $calcId]);
        if (!$found) {
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
                    $this->dao->create($entity);
                }
            }
        }
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

    private function qualRankLink($calcId, $rankId, $ruleId)
    {
        $qual = new EPlanQual();
        $qual->calc_ref = $calcId;
        $qual->rank_ref = $rankId;
        $qual->rule_ref = $ruleId;
        $this->dao->create($qual);
    }

    public function qualRules($calcId, $ranks)
    {
        $found = $this->dao->getOne(EPlanQual::class, [EPlanQual::CALC_REF => $calcId]);
        if (!$found) {
            $ruleIdHuman = $this->qualRulesHuman();
            $this->qualRankLink($calcId, $ranks[0], $ruleIdHuman);
            $ruleIdHero = $this->qualRulesHero();
            $this->qualRankLink($calcId, $ranks[1], $ruleIdHero);
            $ruleIdAngel = $this->qualRulesAngel($ranks);
            $this->qualRankLink($calcId, $ranks[2], $ruleIdAngel);
            $ruleIdGod = $this->qualRulesGod($ranks);
            $this->qualRankLink($calcId, $ranks[3], $ruleIdGod);
        }
    }

    /**
     * Create qualification rules for ANGEL rank.
     *
     * @param int[] $ranks
     * @return int
     */
    private function qualRulesAngel($ranks)
    {
        /* simple PV rules */
        $rulePvId = $this->qualRulesPv(400, 0, false);
        $ruleApvId = $this->qualRulesPv(300, 1, true);
        $ids = [$rulePvId, $ruleApvId];
        /* grouping rule for PV rules */
        $groupPv = $this->qualRulesGroup(Cfg::RULE_GROUP_LOGIC_OR, $ids);
        /* simple rank rule */
        $rankIdHero = $ranks[1];
        $ruleRankId = $this->qualRulesRank($rankIdHero, 3, 0);
        /* final grouping rule */
        $ids = [$groupPv, $ruleRankId];
        $result = $this->qualRulesGroup(Cfg::RULE_GROUP_LOGIC_AND, $ids);

        return $result;
    }

    /**
     * Create qualification rules for GOD rank.
     *
     * @param int[] $ranks
     * @return int
     */
    private function qualRulesGod($ranks)
    {
        /* simple PV rules */
        $rulePvId = $this->qualRulesPv(800, 0, false);
        $ruleApvId = $this->qualRulesPv(600, 1, true);
        $ids = [$rulePvId, $ruleApvId];
        /* grouping rule for PV rules */
        $groupPv = $this->qualRulesGroup(Cfg::RULE_GROUP_LOGIC_OR, $ids);
        /* simple rank rule */
        $rankIdHero = $ranks[1];
        $ruleRankId = $this->qualRulesRank($rankIdHero, 2, 1);
        /* final grouping rule */
        $ids = [$groupPv, $ruleRankId];
        $result = $this->qualRulesGroup(Cfg::RULE_GROUP_LOGIC_AND, $ids);

        return $result;
    }

    private function qualRulesGroup($logic, $otherIds)
    {
        /* register new rule in rule registry */
        $root = new  EQualRule();
        $root->type = Cfg::QUAL_RULE_TYPE_GROUP;
        $result = $this->dao->create($root);

        /* add grouping rule */
        $group = new  EQualRuleGroup();
        $group->ref = $result;
        $group->logic = $logic;
        $this->dao->create($group);

        /*save references to grouping rules */
        foreach ($otherIds as $one) {
            $ref = new  EQualRuleGroupRef();
            $ref->grouping_ref = $result;
            $ref->grouped_ref = $one;
            $this->dao->create($ref);
        }
        return $result;
    }

    /**
     * Create qualification rules for HERO rank.
     *
     * @return int
     */
    private function qualRulesHero()
    {
        /* simple rules */
        $rulePvId = $this->qualRulesPv(200, 0, false);
        $ruleApvId = $this->qualRulesPv(150, 1, true);
        $ids = [$rulePvId, $ruleApvId];
        /* grouping rule*/
        $result = $this->qualRulesGroup(Cfg::RULE_GROUP_LOGIC_OR, $ids);
        return $result;
    }

    /**
     * Create qualification rules for HUMAN rank.
     *
     * @return int
     */
    private function qualRulesHuman()
    {
        /* simple rules */
        $rulePvId = $this->qualRulesPv(100, 0, false);
        $ruleApvId = $this->qualRulesPv(75, 1, true);
        $ids = [$rulePvId, $ruleApvId];
        /* grouping rule*/
        $result = $this->qualRulesGroup(Cfg::RULE_GROUP_LOGIC_OR, $ids);
        return $result;
    }

    /**
     * Create PV based rule for rank qualification.
     *
     * @param float $volume
     * @param int $period
     * @param bool $isAutoship
     * @return int ID of the new PV rule.
     */
    private function qualRulesPv($volume, $period, $isAutoship = false)
    {
        /* register new rule in rule registry */
        $rule = new  EQualRule();
        $rule->type = Cfg::QUAL_RULE_TYPE_PV;
        $result = $this->dao->create($rule);

        /* save rule details */
        $details = new EQualRulePv();
        $details->ref = $result;
        $details->volume = $volume;
        $details->autoship_only = $isAutoship;
        $details->period = $period;
        $this->dao->create($details);
        return $result;
    }

    /**
     * Create rank based rule for rank qualification.
     *
     * @param int $rankId
     * @param int $count
     * @param int $period
     * @return int ID of the new rank rule.
     */
    private function qualRulesRank($rankId, $count, $period)
    {
        /* register new rule in rule registry */
        $rule = new  EQualRule();
        $rule->type = Cfg::QUAL_RULE_TYPE_RANK;
        $result = $this->dao->create($rule);

        /* save rule details */
        $details = new EQualRuleRank();
        $details->ref = $result;
        $details->rank_ref = $rankId;
        $details->count = $count;
        $details->period = $period;
        $this->dao->create($details);
        return $result;
    }

    public function suiteCalcsBinary($suiteId, $typeIds)
    {
        $result = [];
        $key = [ESuiteCalc::SUITE_REF => $suiteId];
        /** @var ESuiteCalc[] $found */
        $found = $this->dao->getSet(ESuiteCalc::class, $key);
        if (!count($found)) {
            $i = 1;
            /* COLLECT */
            $typeCode = Cfg::CALC_TYPE_CV_COLLECT;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
            /* BINARY TREE */
            $typeCode = Cfg::CALC_TYPE_TREE_BINARY;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
            /* PV GROUPING */
            $typeCode = Cfg::CALC_TYPE_CV_GROUPING_PV;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
            /* RANK QUALIFICATION */
            $typeCode = Cfg::CALC_TYPE_RANK_QUAL;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
            /* BINARY COMMISSION */
            $typeCode = Cfg::CALC_TYPE_COMM_BINARY;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
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

    public function suiteCalcsUnilevel($suiteId, $typeIds)
    {
        $result = [];
        $key = [ESuiteCalc::SUITE_REF => $suiteId];
        /** @var ESuiteCalc[] $found */
        $found = $this->dao->getSet(ESuiteCalc::class, $key);
        if (!count($found)) {
            $i = 1;
            /* COLLECT */
            $typeCode = Cfg::CALC_TYPE_CV_COLLECT;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
            /* NATURAL TREE */
            $typeCode = Cfg::CALC_TYPE_TREE_NATURAL;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
            /* PV GROUPING */
            $typeCode = Cfg::CALC_TYPE_CV_GROUPING_PV;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
            /* RANK QUALIFICATION */
            $typeCode = Cfg::CALC_TYPE_RANK_QUAL;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
            /* LEVEL BASED COMMISSION */
            $typeCode = Cfg::CALC_TYPE_COMM_LEVEL_BASED;
            $typeId = $typeIds[$typeCode];
            $calc = new ESuiteCalc();
            $calc->suite_ref = $suiteId;
            $calc->type_ref = $typeId;
            $calc->date_created = new \DateTime();
            $calc->sequence = $i++;
            $id = $this->dao->create($calc);
            $result[$typeCode] = $id;
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

    public function suiteMonth($planId, $note): ESuite
    {
        $key = [ESuite::PLAN_REF => $planId];
        $result = $this->dao->getOne(ESuite::class, $key);
        if (!$result) {
            $suite = new ESuite();
            $suite->plan_ref = $planId;
            $suite->date_created = $this->hlpFormat->getDateNowUtc();
            $suite->period = Cfg::BONUS_PERIOD_TYPE_MONTH;
            $suite->note = $note;
            $this->dao->create($suite);
            $result = $this->dao->getOne(ESuite::class, $key);
        }
        return $result;
    }

    public function suiteWeek($planId, $note): ESuite
    {
        $key = [ESuite::PLAN_REF => $planId];
        $result = $this->dao->getOne(ESuite::class, $key);
        if (!$result) {
            $suite = new ESuite();
            $suite->plan_ref = $planId;
            $suite->date_created = $this->hlpFormat->getDateNowUtc();
            $suite->period = Cfg::BONUS_PERIOD_TYPE_WEEK;
            $suite->note = $note;
            $this->dao->create($suite);
            $result = $this->dao->getOne(ESuite::class, $key);
        }
        return $result;
    }
}