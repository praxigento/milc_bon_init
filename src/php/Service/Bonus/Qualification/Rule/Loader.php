<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule as EQualRule;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Group as ERuleGroup;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Group\Ref as ERuleGroupRef;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Pv as ERulePv;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rule\Rank as ERuleRank;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader\Response as AResponse;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Z\Data\Rule\Group as DGroup;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Z\Data\Rule\Pv as DPv;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Z\Data\Rule\Rank as DRank;

/**
 * Internal service to load qualification rules and create rules tree.
 */
class Loader
{
    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Map */
    private $hlpMap;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn,
        \Praxigento\Milc\Bonus\Api\Helper\Map $hlpMap
    ) {
        $this->conn = $conn;
        $this->hlpMap = $hlpMap;
    }

    /**
     * @param int $rootId
     * @param EQualRule[] $rules
     * @param array $types
     * @return mixed
     */
    private function compose($rootId, $rules, $types)
    {

        $item = $rules[$rootId];
        $type = $item->type;
        if ($type == Cfg::QUAL_RULE_TYPE_GROUP) {
            /** @var DGroup $group */
            $group = $types[Cfg::QUAL_RULE_TYPE_GROUP][$rootId];
            $logic = $group->logic;
            $grouped = [];
            foreach ($group->rules as $innerRuleId) {
                $innerRule = $this->compose($innerRuleId, $rules, $types);
                $grouped[] = $innerRule;
            }
            $result = new DGroup();
            $result->id = $rootId;
            $result->logic = $logic;
            $result->rules = $grouped;
        } elseif ($type == Cfg::QUAL_RULE_TYPE_PV) {
            $rule = $types[Cfg::QUAL_RULE_TYPE_PV][$rootId];
            $result = new DPv($rule);
        } elseif ($type == Cfg::QUAL_RULE_TYPE_RANK) {
            $rule = $types[Cfg::QUAL_RULE_TYPE_RANK][$rootId];
            $result = new DRank($rule);
        } else {
            $result = null;
        }
        return $result;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $rootIds = $req->rootIds;

        /* load rules registry */
        $rules = $this->loadRules();
        /* then load rules definitions by type */
        $groups = $this->loadTypeGroup();
        $pvs = $this->loadTypePv();
        $ranks = $this->loadTypeRank();
        $types = [];
        $types[Cfg::QUAL_RULE_TYPE_GROUP] = $groups;
        $types[Cfg::QUAL_RULE_TYPE_PV] = $pvs;
        $types[Cfg::QUAL_RULE_TYPE_RANK] = $ranks;

        /* create rules tree */
        $trees = [];
        foreach ($rootIds as $rootId) {
            $tree = $this->compose($rootId, $rules, $types);
            $trees[$rootId] = $tree;
        }
        $result = new AResponse();
        $result->trees = $trees;
        return $result;
    }

    private function loadRules()
    {
        $as = 'rule';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_CALC_QUAL_RULE, $as);
        $qb->select("$as.*");
        $stmt = $qb->execute();
        /** @var EQualRule[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, EQualRule::class);
        $result = $this->hlpMap->byId($all, EQualRule::ID);
        return $result;
    }

    private function loadTypeGroup()
    {
        $result = [];
        $asMain = 'main';
        $asOther = 'other';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_CALC_QUAL_RULE_GROUP, $asMain);
        $qb->select([
            "$asMain." . ERuleGroup::REF . " as " . DGroup::ID,
            "$asMain." . ERuleGroup::LOGIC . " as " . DGroup::LOGIC,
            "$asOther." . ERuleGroupRef::GROUPED_REF . " as " . DGroup::RULES
        ]);
        /* LEFT JOIN bon_calc_qual_rank */
        $on = "$asOther." . ERuleGroupRef::GROUPING_REF . "=$asMain." . ERuleGroup::REF;
        $qb->leftJoin($asMain, Cfg::DB_TBL_BON_CALC_QUAL_RULE_GROUP_REF, $asOther, $on);

        $stmt = $qb->execute();
        /** @var EQualRule[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::ASSOCIATIVE);
        foreach ($all as $one) {
            $ruleId = $one[DGroup::ID];
            $logic = $one[DGroup::LOGIC];
            $other = $one[DGroup::RULES];
            if (!isset($result[$ruleId])) {
                $item = new DGroup();
                $item->id = $ruleId;
                $item->logic = $logic;
                $item->rules = [$other];
            } else {
                $item = $result[$ruleId];
                $item->rules[] = $other;
            }
            $result[$ruleId] = $item;
        }
        return $result;
    }

    private function loadTypePv()
    {
        $result = [];
        $as = 'main';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_CALC_QUAL_RULE_PV, $as);
        $qb->select('*');
        $stmt = $qb->execute();
        /** @var ERulePv[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, DPv::class);
        foreach ($all as $one) {
            $ruleId = $one->ref;
            $result[$ruleId] = $one;
        }
        return $result;
    }

    private function loadTypeRank()
    {
        $result = [];
        $as = 'main';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_CALC_QUAL_RULE_RANK, $as);
        $qb->select('*');
        $stmt = $qb->execute();
        /** @var ERuleRank[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, DRank::class);
        foreach ($all as $one) {
            $ruleId = $one->ref;
            $result[$ruleId] = $one;
        }
        return $result;
    }
}