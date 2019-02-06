<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Qualification\Rank as EQualRank;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Tree as ETree;
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
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Map */
    private $hlpMap;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Db\Query\GetRanks */
    private $qGetRanks;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader */
    private $srvLoader;

    public function __construct(
        \Praxigento\Milc\Bonus\Api\Helper\Map $hlpMap,
        \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Db\Query\GetRanks $qGetRanks,
        \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader $srvLoader
    ) {
        $this->hlpMap = $hlpMap;
        $this->qGetRanks = $qGetRanks;
        $this->srvLoader = $srvLoader;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $calcInstId = $req->calcInstId;
        $tree = $req->tree;

        /** @var DRankEntry[] $ranks */
        $ranks = $this->getRanks($calcInstId);
        $rules = $this->getRulesTree($ranks);

        $entries = [];
        /** @var \Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Tree $one */
        foreach ($tree as $one) {
            $clientId = $one->client_ref;
            $rankId = $this->qualify($one, $ranks, $rules);
            if ($rankId) {
                $entry = new EQualRank();
                $entry->calc_inst_ref = $calcInstId;
                $entry->client_ref = $clientId;
                $entry->rank_ref = $rankId;
                $entries[] = $entry;
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
            $rootRules[] = $rank->ruleId;
        }

        $req = new ARuleLoadRequest();
        $req->rootIds = $rootRules;
        /** @var ARuleLoadResponse $resp */
        $resp = $this->srvLoader->exec($req);
        $result = $resp->trees;
        return $result;
    }

    /**
     * @param ETree $one
     * @param DRankEntry[] $ranks
     * @param array $rules
     * @return null
     */
    private function qualify($one, $ranks, $rules)
    {
        $result = null;
        foreach ($ranks as $rank) {
            $ruleId = $rank->ruleId;
            $chain = $rules[$ruleId];
            $isValid = $this->validateRules($one, $chain);
            if ($isValid) {
                $result = $rank->rankId;
            } else {
                break;
            }
        }
        return $result;
    }

    /**
     * @param ETree $client
     * @param $chain
     * @return bool
     */
    private function validateRules($client, $chain)
    {
        $result = false;

        $class = get_class($chain);
        if ($class == DGroup::class) {
            $result = $this->validateRulesGroup($client, $chain);
        } elseif ($class == DPv::class) {
            $result = $this->validateRulesPv($client, $chain);
        } elseif ($class == DRank::class) {
            $result = $this->validateRulesRank($client, $chain);
        }
        return $result;
    }

    /**
     * @param ETree $client
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
     * @param ETree $treeNode
     * @param DPv $rule
     * @return bool
     */
    private function validateRulesPv($treeNode, $rule)
    {
        $result = false;
        $clientId = $treeNode->client_ref;
        if ($rule->period == 0) {
            /* get current period PV/APV */
            $pv = $treeNode->pv;
            $apv = $treeNode->apv;
        } else {
            /* retrieve qualification data for period in the past */
            /* TODO: add data loading for past periods */
            $pv = 0;
            $apv = 0;
        }
        if ($rule->autoship_only) {
            /* compare autoship PV */
            $result = $apv >= $rule->volume;
        } else {
            /* compare all PV */
            $result = $pv >= $rule->volume;
        }
        return $result;
    }

    /**
     * @param ETree $client
     * @param DRank $chain
     * @return bool
     */
    private function validateRulesRank($client, $chain)
    {
        $result = false;
        return $result;
    }
}