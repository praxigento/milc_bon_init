<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Comm\Level as ECalcLevel;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Calc as EPoolCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Comm\Level as ECommLevel;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Comm\Level\Quant as ECommLevelQuant;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Rank as EPoolRank;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as EPoolTree;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Data\PvEntry as DTreePv;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Data\PvLinkEntry as DTreePvLink;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query\GetPv as QGetPv;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query\GetPvLinks as QGetPvLinks;
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
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query\GetPv */
    private $qGetPv;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query\GetPvLinks */
    private $qGetPvLinks;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn,
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Api\Helper\Map $hlpMap,
        \Praxigento\Milc\Bonus\Api\Helper\Tree $hlpTree,
        \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query\GetPv $qGetPv,
        \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query\GetPvLinks $qGetPvLinks
    ) {
        $this->conn = $conn;
        $this->dao = $dao;
        $this->hlpMap = $hlpMap;
        $this->hlpTree = $hlpTree;
        $this->qGetPv = $qGetPv;
        $this->qGetPvLinks = $qGetPvLinks;
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
        $pvs = $this->getPv($poolCalcIdTree);
        $pvLinks = $this->getPvLinks($poolCalcIdTree);
        $fullTree = $this->hlpTree->expandMinimal($tree, EPoolTree::CLIENT_REF, EPoolTree::PARENT_REF);
        $treeByDepthDesc = $this->hlpTree->mapByDepthDesc($fullTree);

        foreach ($treeByDepthDesc as $level) {
            foreach ($level as $clientId) {
                if (!isset($tree[$clientId]))
                    throw new \Exception("Client with ID $clientId is not found in the tree (CV collection).");
                /** @var EPoolTree $node */
                $node = $tree[$clientId];
                $pv = 0;
                if (isset($pvs[$clientId])) {
                    /** @var DTreePv $nodePv */
                    $nodePv = $pvs[$clientId];
                    $pv = $nodePv->pv;
                }
                if (abs($pv) > Cfg::ZERO) {
                    /* propagate PV up in the tree by levels */
                    $currentId = $clientId;
                    $currentQuants = $pvLinks[$clientId];
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
                        /** @var EPoolTree $parent */
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
        $qb->where(EPoolCalc::ID . "=:$bndId");
        $qb->setParameters([$bndId => $poolCalcId]);
        $stmt = $qb->execute();
        /** @var EPoolCalc[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, EPoolCalc::class);
        /** @var EPoolCalc $one */
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
     * @param $poolCalcIdTree
     * @return EPoolTree[]
     */
    private function getPv($poolCalcIdTree)
    {
        $query = $this->qGetPv->build();
        $bind = [QGetPv::BND_POOL_CALC_ID => $poolCalcIdTree];
        $query->setParameters($bind);
        $stmt = $query->execute();
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, QGetPv::RESULT_CLASS);
        $result = [];
        /** @var DTreePv $one */
        foreach ($all as $one) {
            $clientId = $one->client_id;
            $result[$clientId] = $one;
        }
        return $result;
    }

    private function getPvLinks($poolCalcIdTree)
    {
        $query = $this->qGetPvLinks->build();
        $bind = [QGetPvLinks::BND_POOL_CALC_ID => $poolCalcIdTree];
        $query->setParameters($bind);
        $stmt = $query->execute();
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, QGetPvLinks::RESULT_CLASS);
        $result = [];
        /** @var DTreePvLink $item */
        foreach ($all as $item) {
            $clientId = $item->client_id;
            $regId = $item->cv_reg_id;
            $cv = $item->volume;
            $result[$clientId][$regId] = $cv;
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
        $qb->where(EPoolRank::POOL_CALC_REF . "=:$bndId");
        $qb->setParameters([$bndId => $poolCalcId]);
        $stmt = $qb->execute();
        /** @var EPoolRank[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, EPoolRank::class);
        $result = [];
        foreach ($all as $one) {
            $clientId = $one->client_ref;
            $rankId = $one->rank_ref;
            $result[$clientId] = $rankId;
        }
        return $result;
    }

    /**
     * @param $poolCalcIdTree
     * @return EPoolTree[]
     */
    private function getTree($poolCalcIdTree)
    {
        $bndPoolCalcId = 'pool_calc_id';
        $bind = [$bndPoolCalcId => $poolCalcIdTree];
        $where = EPoolTree::POOL_CALC_REF . "=:$bndPoolCalcId";
        $all = $this->dao->getSet(EPoolTree::class, $bind, $where);
        $result = [];
        foreach ($all as $one) {
            $clientId = $one->client_ref;
            $result[$clientId] = $one;
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