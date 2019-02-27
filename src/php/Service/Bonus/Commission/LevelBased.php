<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Comm\Level as ECalcLevel;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Calc as EPeriodCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Comm\Level as ECommLevel;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Comm\Level\Quant as ECommLevelQuant;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Rank as EPeriodRank;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as EPeriodTree;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Data\TreeQuant as DTreeQuant;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query\GetTreeQuants as QGetTreeQuants;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Response as AResponse;

class LevelBased
{
    private const KEY_COMM = 'comm';
    private const KEY_LINKED = 'linked';
    private const KEY_PV = 'pv';
    private const KEY_QUANTS = 'quants';

    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Map */
    private $hlpMap;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Tree */
    private $hlpTree;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query\GetTreeQuants */
    private $qGetTreeQuants;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn,
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Api\Helper\Map $hlpMap,
        \Praxigento\Milc\Bonus\Api\Helper\Tree $hlpTree,
        \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query\GetTreeQuants $qGetTreeQuants
    ) {
        $this->conn = $conn;
        $this->dao = $dao;
        $this->hlpMap = $hlpMap;
        $this->hlpTree = $hlpTree;
        $this->qGetTreeQuants = $qGetTreeQuants;
    }

    /**
     * Collect commission and related quants.
     *
     * @param $calcInstId
     * @param $ranks
     * @param $commByRanks
     * @param $mapCvByLevel
     * @return array
     */
    private function collectCommWithQuants($calcInstId, $ranks, $commByRanks, $mapCvByLevel)
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
                    $cv = $cvByLevels[$level][self::KEY_PV];
                    $comm = round($cv * $percent, 2);
                    $entityComm = new ECommLevel();
                    $entityComm->pool_calc_ref = $calcInstId;
                    $entityComm->client_ref = $clientId;
                    $entityComm->level = $level;
                    $entityComm->cv = $cv;
                    $entityComm->percent = $percent;
                    $entityComm->commission = $comm;
                    $quants = $cvByLevels[$level][self::KEY_QUANTS];
                    $quantsToLink = [];
                    $commToCv = $comm / $cv;
                    $checkSum = 0;
                    foreach ($quants as $cvRegId => $volume) {
                        $quantValue = round($commToCv * $volume, 2);
                        $entityQuant = new ECommLevelQuant();
                        $entityQuant->cv_reg_ref = $cvRegId;
                        $entityQuant->value = $quantValue;
                        $quantsToLink[] = $entityQuant;
                        $checkSum += $quantValue;
                    }
                    $result[] = [self::KEY_COMM => $entityComm, self::KEY_LINKED => $quantsToLink];
                }
            }
        }
        return $result;
    }

    /**
     * @param int $poolCalcIdTree
     * @return array [nodeId][level]=>sumCv
     * @throws \Exception
     */
    private function collectCvByLevel($poolCalcIdTree)
    {
        $result = [];
        $tree = $this->getTree($poolCalcIdTree);
        $quants = $this->getTreeQuants($poolCalcIdTree);
        $fullTree = $this->hlpTree->expandMinimal($tree, EPeriodTree::CLIENT_REF, EPeriodTree::PARENT_REF);
        $treeByDepthDesc = $this->hlpTree->mapByDepthDesc($fullTree);

        foreach ($treeByDepthDesc as $level) {
            foreach ($level as $clientId) {
                if (!isset($tree[$clientId]))
                    throw new \Exception("Client with ID $clientId is not found in the tree (CV collection).");
                /** @var EPeriodTree $node */
                $node = $tree[$clientId];
                $pv = $node->pv;
                if (abs($pv) > Cfg::ZERO) {
                    /* propagate PV up in the tree by levels */
                    $currentId = $clientId;
                    $currentQuants = $quants[$clientId];
                    $parentId = $node->parent_ref;
                    $level = 1;
                    while ($parentId != $currentId) {
                        if (!isset($result[$parentId][$level])) {
                            $result[$parentId][$level][self::KEY_PV] = 0;
                            $result[$parentId][$level][self::KEY_QUANTS] = [];
                        }
                        $result[$parentId][$level][self::KEY_PV] += $pv;
                        $existingQuants = $result[$parentId][$level][self::KEY_QUANTS];
                        /* use 'replace' instead of 'merge' cause numeric keys will be reset in 'merge' */
                        $mergedQuants = array_replace($existingQuants, $currentQuants);
                        $result[$parentId][$level][self::KEY_QUANTS] = $mergedQuants;

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
        $poolCalcIdCv = $req->poolCalcIdCv;
        $poolCalcIdOwn = $req->poolCalcIdOwn;
        $poolCalcIdRanks = $req->poolCalcIdRanks;
        $poolCalcIdTree = $req->poolCalcIdTree;

        $calcId = $this->getCalcId($poolCalcIdOwn);
        $commByRanks = $this->getLevels($calcId);
        $ranks = $this->getRanks($poolCalcIdRanks);
        $mapCvByLevel = $this->collectCvByLevel($poolCalcIdTree);

        $commWithQuants = $this->collectCommWithQuants($poolCalcIdOwn, $ranks, $commByRanks, $mapCvByLevel);
        $this->saveCommWithQuants($commWithQuants);

        $result = new AResponse();
        return $result;
    }

    /**
     * Get calculation ID for calculation instance (calc-in-period).
     *
     * @param int $poolCalcId
     * @return int
     */
    private function getCalcId($poolCalcId)
    {
        $as = 'main';
        $bndId = 'id';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_POOL_CALC, $as);
        $qb->select("$as.*");
        $qb->where(EPeriodCalc::ID . "=:$bndId");
        $qb->setParameters([$bndId => $poolCalcId]);
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
     * @param int $poolCalcId
     * @return array [$clientId] = $rankId
     */
    private function getRanks($poolCalcId)
    {
        $as = 'main';
        $bndId = 'id';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_POOL_RANK, $as);
        $qb->select("$as.*");
        $qb->where(EPeriodRank::POOL_CALC_REF . "=:$bndId");
        $qb->setParameters([$bndId => $poolCalcId]);
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
     * @param $poolCalcId
     * @return EPeriodTree[]
     */
    private function getTree($poolCalcId)
    {
        $as = 'main';
        $bndId = 'id';
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_POOL_TREE, $as);
        $qb->select("$as.*");
        $qb->where(EPeriodTree::POOL_CALC_REF . "=:$bndId");
        $qb->setParameters([$bndId => $poolCalcId]);
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

    private function getTreeQuants($poolCalcId)
    {
        $query = $this->qGetTreeQuants->build();
        $bind = [QGetTreeQuants::BND_POOL_CALC_ID => $poolCalcId];
        $query->setParameters($bind);
        $stmt = $query->execute();
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, QGetTreeQuants::RESULT_CLASS);
        $result = [];
        /** @var DTreeQuant $item */
        foreach ($all as $item) {
            $clientId = $item->client_id;
            $regId = $item->cv_reg_id;
            $cv = $item->volume;
            $result[$clientId][$regId] = $cv;
        }
        return $result;
    }

    private function saveCommWithQuants($commWithQuants)
    {
        /* save commissions to DB */
        foreach ($commWithQuants as $item) {
            /** @var ECommLevel $comm */
            $comm = $item[self::KEY_COMM];
            $id = $this->dao->create($comm);
            $quants = $item[self::KEY_LINKED];
            /** @var ECommLevelQuant $quant */
            foreach ($quants as $quant) {
                $quant->comm_ref = $id;
                $this->dao->create($quant);
            }
        }

    }
}