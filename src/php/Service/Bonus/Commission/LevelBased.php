<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Period\Calc as EPeriodCalc;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Period\Rank as EPeriodRank;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Period\Tree as EPeriodTree;
use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Plan\Level as EPlanLevel;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Response as AResponse;

class LevelBased
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

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $thisCalcInstId = $req->thisCalcInstId;
        $ranksCalcInstId = $req->ranksCalcInstId;
        $treeCalcInstId = $req->treeCalcInstId;

        $calcId = $this->getCalcId($thisCalcInstId);
        $levels = $this->getLevels($calcId);
        $ranks = $this->getRanks($ranksCalcInstId);
        $tree = $this->getTree($treeCalcInstId);

        $result = new AResponse();
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
        $qb->from(Cfg::DB_TBL_BON_PERIOD_CALC, $as);
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
        $qb->from(Cfg::DB_TBL_BON_PLAN_LEVEL, $as);
        $qb->select("$as.*");
        $qb->where(EPlanLevel::CALC_REF . "=:$bndId");
        $qb->setParameters([$bndId => $calcId]);
        $stmt = $qb->execute();
        /** @var EPlanLevel[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, EPlanLevel::class);
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
        $qb->from(Cfg::DB_TBL_BON_PERIOD_RANK, $as);
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
        $qb->from(Cfg::DB_TBL_BON_PERIOD_TREE, $as);
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