<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Rule;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Rank\Rule\Pv as ERulePv;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Calc as EPoolCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as ETree;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Rule\Pv\A\Data\PvEntry as DEntryPv;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Rule\Pv\A\Db\Query\GetTreePv as QGetTreePv;

class Pv
{
    /** @var int */
    private $cachePoolIdCurrent;
    /** @var array */
    private $cachePvs = [];
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Rule\Pv\A\Db\Query\GetTreePv */
    private $qGetTreePv;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Rule\Pv\A\Db\Query\GetTreePv $qGetTreePv
    ) {
        $this->dao = $dao;
        $this->qGetTreePv = $qGetTreePv;
    }

    /**
     * @param int $poolCalcIdTree
     * @return QGetTreePv[] [clientId => item]
     */
    private function loadPv($poolCalcIdTree)
    {
        $query = $this->qGetTreePv->build();
        $bind = [QGetTreePv::BND_POOL_CALC_ID => $poolCalcIdTree];
        $query->setParameters($bind);
        $stmt = $query->execute();
        /** @var DEntryPv[] $all */
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, QGetTreePv::RESULT_CLASS);
        $result = [];
        foreach ($all as $one) {
            $clientId = $one->client_id;
            $result[$clientId] = $one;
        }
        return $result;
    }

    /**
     * Reset rule validator state before processing rules. Load PVs for current pool tree.
     *
     * @param $poolCalcIdTree
     */
    public function reset($poolCalcIdTree)
    {
        /** @var EPoolCalc $poolCalc */
        $poolCalc = $this->dao->getOne(EPoolCalc::class, $poolCalcIdTree);
        $this->cachePoolIdCurrent = $poolCalc->pool_ref;
        /* reset PV cache is related to the tree and load current pull PVs */
        $this->cachePvs = [];
        $this->cachePvs[0] = $this->loadPv($poolCalcIdTree);
    }

    /**
     * @param ETree $treeNode
     * @param ERulePv $rule
     * @return bool
     */
    public function validate($treeNode, $rule)
    {
        $result = false;
        $period = $rule->period;
        $nodeId = $treeNode->client_ref;
        $clientId = $treeNode->client_ref;
        if ($rule->period == 0) {
            /* get current period PV/APV */
            $periodPvs = $this->cachePvs[0];
            /** @var DEntryPv $entry */
            $entry = $periodPvs[$clientId] ?? null;
            if ($entry) {
                $pv = $entry->pv;
                $apv = $entry->apv;
            } else {
                $pv = $apv = 0;
            }
        } else {
            /* retrieve qualification data for period in the past */
            /* TODO: add data loading for past periods */
            $pv = 0;
            $apv = 0;
        }
        if ($rule->autoship_only) {
            /* compare autoship PV */
            $result = ($apv >= $rule->volume);
        } else {
            /* compare all PV */
            $result = ($pv >= $rule->volume);
        }
        return $result;
    }
}