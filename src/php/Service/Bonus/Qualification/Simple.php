<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification;

use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader\Request as ARuleLoadRequest;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader\Response as ARuleLoadResponse;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Data\RankEntry as DRankEntry;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Db\Query\GetRanks as QGetRanks;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Response as AResponse;

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
        $rulesTree = $this->getRulesTree($ranks);

        /** @var \Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Tree $one */
        foreach ($tree as $one) {
            $clientId = $one->client_ref;
            $rankId = $this->boo($one, $ranks);
            $q = 4;
        }
        $result = new AResponse();
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
    }
}