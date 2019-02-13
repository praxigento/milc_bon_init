<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Comm\Level as ECalcLevel;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Calc as EPeriodCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Level as EPeriodLevel;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Rank as EPeriodRank;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Tree as EPeriodTree;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Response as AResponse;

class LevelBased
{
    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Map */
    private $hlpMap;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Tree */
    private $hlpTree;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn,
        \Praxigento\Milc\Bonus\Api\Helper\Map $hlpMap,
        \Praxigento\Milc\Bonus\Api\Helper\Tree $hlpTree
    ) {
        $this->conn = $conn;
        $this->hlpMap = $hlpMap;
        $this->hlpTree = $hlpTree;
    }

    private function collectCommission($calcInstId, $ranks, $commByRanks, $mapCvByLevel)
    {
        $result = [];
        foreach ($ranks as $clientId => $rank) {
            if (
                !isset($commByRanks[$rank]) ||
                !isset($mapCvByLevel[$clientId])
            ) {
                continue;
            }
            $commByLevels = $commByRanks[$rank];
            $cvByLevels = $mapCvByLevel[$clientId];
            foreach ($commByLevels as $level => $percent) {
                if (isset($cvByLevels[$level])) {
                    $cv = $cvByLevels[$level];
                    $comm = round($cv * $percent, 2);
                    $entry = new EPeriodLevel();
                    $entry->calc_inst_ref = $calcInstId;
                    $entry->client_ref = $clientId;
                    $entry->level = $level;
                    $entry->cv = $cv;
                    $entry->percent = $percent;
                    $entry->commission = $comm;
                    $result[] = $entry;
                }
            }
        }
        return $result;
    }

    /**
     * @param int $treeCalcInstId
     * @return array [nodeId][level]=>sumCv
     * @throws \Exception
     */
    private function collectCvByLevel($treeCalcInstId)
    {
        $result = [];
        $tree = $this->getTree($treeCalcInstId);
        $fullTree = $this->hlpTree->expandMinimal($tree, EPeriodTree::CLIENT_REF, EPeriodTree::PARENT_REF);
        $treeByDepthDesc = $this->hlpTree->mapByDepthDesc($fullTree);

        foreach ($treeByDepthDesc as $level) {
            foreach ($level as $nodeId) {
                if (!isset($tree[$nodeId]))
                    throw new \Exception("Node with ID $nodeId is not found in the tree (CV collection).");
                /** @var EPeriodTree $node */
                $node = $tree[$nodeId];
                $pv = $node->pv;
                if (abs($pv) > Cfg::ZERO) {
                    /* propagate PV up in the tree by levels */
                    $currentId = $nodeId;
                    $parentId = $node->parent_ref;
                    $level = 1;
                    while ($parentId != $currentId) {
                        if (!isset($result[$parentId][$level])) {
                            $result[$parentId][$level] = $pv;
                        } else {
                            $result[$parentId][$level] += $pv;
                        }
                        /* move one step up */
                        if (!isset($tree[$parentId]))
                            throw new \Exception("Node with ID $parentId is not found in the tree (CV collection, parent).");
                        /** @var EPeriodTree $parent */
                        $parent = $tree[$parentId];
                        $currentId = $parentId;
                        $parentId = $parent->parent_ref;
                        $level++;
                    }
                }
            }
        }
        return $result;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $thisCalcInstId = $req->thisCalcInstId;
        $ranksCalcInstId = $req->ranksCalcInstId;
        $treeCalcInstId = $req->treeCalcInstId;

        $calcId = $this->getCalcId($thisCalcInstId);
        $commByRanks = $this->getLevels($calcId);
        $ranks = $this->getRanks($ranksCalcInstId);
        $mapCvByLevel = $this->collectCvByLevel($treeCalcInstId);

        $comm = $this->collectCommission($thisCalcInstId, $ranks, $commByRanks, $mapCvByLevel);

        $result = new AResponse();
        $result->commissions = $comm;
        return $result;
    }

    /**
     * Get calculation ID for calculation instance (calc-in-period).
     *
     * @param int $calcInstId
     * @return int
     */
    private function getCalcId($calcInstId)
    {
        $as = 'main';
        $bndId = 'id';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_RESULT_CALC, $as);
        $qb->select("$as.*");
        $qb->where(EPeriodCalc::ID . "=:$bndId");
        $qb->setParameters([$bndId => $calcInstId]);
        $stmt = $qb->execute();
        /** @var EPeriodCalc[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, EPeriodCalc::class);
        /** @var EPeriodCalc $one */
        $one = reset($all);
        $result = $one->calc_ref;
        return $result;
    }

    /**
     * Get commission levels by rank.
     *
     * @param int $calcId
     * @return array [$rankId][$level] = $percent
     */
    private function getLevels($calcId)
    {
        $as = 'main';
        $bndId = 'id';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_CALC_COMM_LEVEL, $as);
        $qb->select("$as.*");
        $qb->where(ECalcLevel::CALC_REF . "=:$bndId");
        $qb->setParameters([$bndId => $calcId]);
        $stmt = $qb->execute();
        /** @var ECalcLevel[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, ECalcLevel::class);
        $result = [];
        foreach ($all as $one) {
            $rankId = $one->rank_ref;
            $level = $one->level;
            $percent = $one->percent;
            $result[$rankId][$level] = $percent;
        }
        return $result;
    }

    /**
     * @param int $calcInstId
     * @return array [$clientId] = $rankId
     */
    private function getRanks($calcInstId)
    {
        $as = 'main';
        $bndId = 'id';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_RESULT_RANK, $as);
        $qb->select("$as.*");
        $qb->where(EPeriodRank::CALC_INST_REF . "=:$bndId");
        $qb->setParameters([$bndId => $calcInstId]);
        $stmt = $qb->execute();
        /** @var EPeriodRank[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, EPeriodRank::class);
        $result = [];
        foreach ($all as $one) {
            $clientId = $one->client_ref;
            $rankId = $one->rank_ref;
            $result[$clientId] = $rankId;
        }
        return $result;
    }

    /**
     * @param $calcInstId
     * @return EPeriodTree[]
     */
    private function getTree($calcInstId)
    {
        $as = 'main';
        $bndId = 'id';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_RESULT_TREE, $as);
        $qb->select("$as.*");
        $qb->where(EPeriodTree::CALC_INST_REF . "=:$bndId");
        $qb->setParameters([$bndId => $calcInstId]);
        $stmt = $qb->execute();
        /** @var EPeriodTree[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, EPeriodTree::class);
        $result = [];
        foreach ($all as $one) {
            $clientId = $one->client_ref;
            $result[$clientId] = $one;
        }
        return $result;
    }
}