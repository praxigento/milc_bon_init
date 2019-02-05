<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Qualification\Rule as EQualRule;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Qualification\Rule\Group as ERuleGroup;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Qualification\Rule\Group\Ref as ERuleGroupRef;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader\A\Data\Group as DGroup;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader\Response as AResponse;

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
     * @param DGroup[] $groups
     * @return mixed
     */
    private function boo($rootId, $rules, $groups)
    {
        $item = $rules[$rootId];
        $type = $item->type;
        if ($type == Cfg::QUAL_RULE_TYPE_GROUP) {
            /** @var DGroup $group */
            $group = $groups[$rootId];
            $logic = $group->logic;
            foreach ($group->rules as $oneRule) {
                /* TODO: we need to process every rule then compose current node using $logic */
                /* TODO: load all rule types in one registry (array[type]) instead of $group */
            }
        }
        return $item;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $rootIds = $req->rootIds;

        $rules = $this->loadRules();
        $groups = $this->loadTypeGroup();

        $trees = [];
        foreach ($rootIds as $rootId) {
            $tree = $this->boo($rootId, $rules, $groups);
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
        $qb->from(Cfg::DB_TBL_BON_QUAL_RULE, $as);
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
        $qb->from(Cfg::DB_TBL_BON_QUAL_RULE_GROUP, $asMain);
        $qb->select([
            "$asMain." . ERuleGroup::REF . " as " . DGroup::ID,
            "$asMain." . ERuleGroup::LOGIC . " as " . DGroup::LOGIC,
            "$asOther." . ERuleGroupRef::GROUPED_REF . " as " . DGroup::RULES
        ]);
        /* LEFT JOIN bon_plan_qual */
        $on = "$asOther." . ERuleGroupRef::GROUPING_REF . "=$asMain." . ERuleGroup::REF;
        $qb->leftJoin($asMain, Cfg::DB_TBL_BON_QUAL_RULE_GROUP_REF, $asOther, $on);

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
}