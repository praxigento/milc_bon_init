<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Rank as EPeriodRank;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as EPeriodTree;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader\Request as ARuleLoadRequest;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader\Response as ARuleLoadResponse;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Data\RankEntry as DRankEntry;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Db\Query\GetRanks as QGetRanks;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Response as AResponse;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Z\Data\Rule\Group as DGroup;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Z\Data\Rule\Pv as DPv;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Z\Data\Rule\Rank as DRank;

/**
 * Internal service to perform simple qualification calculation (based on PV, ...).
 */
class Simple
{
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Rule\Pv */
    private $aRulePv;
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Map */
    private $hlpMap;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Db\Query\GetRanks */
    private $qGetRanks;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader */
    private $srvLoader;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Api\Helper\Map $hlpMap,
        \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Db\Query\GetRanks $qGetRanks,
        \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader $srvLoader,
        \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Rule\Pv $aRulePv
    ) {
        $this->dao = $dao;
        $this->hlpMap = $hlpMap;
        $this->qGetRanks = $qGetRanks;
        $this->srvLoader = $srvLoader;
        $this->aRulePv = $aRulePv;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $poolCalcIdRank = $req->poolCalcIdRank;
        $poolCalcIdTree = $req->poolCalcIdTree;

        $tree = $this->getTree($poolCalcIdTree);

        /** @var DRankEntry[] $ranks */
        $ranks = $this->getRanks($poolCalcIdRank);
        $rules = $this->getRulesTree($ranks);

        $this->aRulePv->reset($poolCalcIdTree);
        $entries = [];
        /** @var \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree $one */
        foreach ($tree as $one) {
            $clientId = $one->client_ref;
            $rankId = $this->qualify($one, $ranks, $rules);
            if ($rankId) {
                $entity = new EPeriodRank();
                $entity->pool_calc_ref = $poolCalcIdRank;
                $entity->client_ref = $clientId;
                $entity->rank_ref = $rankId;
                $this->dao->create($entity);
                $entries[] = $entity;
            }
        }
        $result = new AResponse();
        $result->entries = $entries;
        return $result;
    }

    /**
     * @param int $calcInstId
     * @return \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Data\RankEntry[]
     */
    private function getRanks($calcInstId)
    {
        $qb = $this->qGetRanks->build();
        $qb->setParameters([QGetRanks::BND_CALC_ID => $calcInstId]);
        $stmt = $qb->execute();
        /** @var DRankEntry[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, DRankEntry::class);
        /** @var DRankEntry[] $result */
        $result = $this->hlpMap->byId($all, DRankEntry::SEQUENCE);
        ksort($result);
        return $result;
    }

    /**
     * @param DRankEntry[] $ranks
     */
    private function getRulesTree($ranks)
    {
        $rootRules = [];
        foreach ($ranks as $rank) {
            $rootRules[] = $rank->rule_id;
        }

        $req = new ARuleLoadRequest();
        $req->rootIds = $rootRules;
        /** @var ARuleLoadResponse $resp */
        $resp = $this->srvLoader->exec($req);
        $result = $resp->trees;
        return $result;
    }

    private function getTree($poolCalcId)
    {
        $key = [
            EPeriodTree::POOL_CALC_REF => $poolCalcId
        ];
        $result = $this->dao->getSet(EPeriodTree::class, $key);
        return $result;
    }

    /**
     * @param EPeriodTree $one
     * @param DRankEntry[] $ranks
     * @param array $rules
     * @return null
     */
    private function qualify($one, $ranks, $rules)
    {
        $result = null;
        foreach ($ranks as $rank) {
            $ruleId = $rank->rule_id;
            $rule = $rules[$ruleId];
            $isValid = $this->validateRules($one, $rule);
            if ($isValid) {
                $result = $rank->rank_id;
            } else {
                break;
            }
        }
        return $result;
    }

    /**
     * @param EPeriodTree $client
     * @param $rule
     * @return bool
     */
    private function validateRules($client, $rule)
    {
        $result = false;

        $class = get_class($rule);
        if ($class == DGroup::class) {
            $result = $this->validateRulesGroup($client, $rule);
        } elseif ($class == DPv::class) {
            $result = $this->aRulePv->validate($client, $rule);
        } elseif ($class == DRank::class) {
            $result = $this->validateRulesRank($client, $rule);
        }
        return $result;
    }

    /**
     * @param EPeriodTree $client
     * @param DGroup $chain
     * @return bool
     */
    private function validateRulesGroup($client, $chain)
    {
        $result = false;
        $logic = $chain->logic;
        $rules = $chain->rules;
        foreach ($rules as $rule) {
            $isStepValid = $this->validateRules($client, $rule);
            if ($logic == Cfg::RULE_GROUP_LOGIC_OR) {
                if ($isStepValid) {
                    /* break on first 'true' result */
                    $result = true;
                    break;
                } else {
                    /* continue OR processing */
                }
            } elseif ($logic == Cfg::RULE_GROUP_LOGIC_OR) {
                if (!$isStepValid) {
                    /* break on first 'false' result */
                    $result = false;
                    break;
                } else {
                    /* continue AND processing */
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * @param EPeriodTree $client
     * @param DRank $chain
     * @return bool
     */
    private function validateRulesRank($client, $chain)
    {
        $result = false;
        return $result;
    }
}