<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Cv\Group;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree\Pv as ETreePv;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree\Quant as ETreePvLink;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\A\Data\Item as DItem;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\A\Db\Query\CollectCv as QCollectCv;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\Response as AResponse;

/**
 * Group (A)PV for given tree.
 */
class Pv
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\A\Db\Query\CollectCv */
    private $qCollectCv;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\A\Db\Query\CollectCv $qCollectCv
    ) {
        $this->dao = $dao;
        $this->qCollectCv = $qCollectCv;
    }

    /**
     * Select all CV movements for the pool and group its by customer.
     *
     * @param int $poolCalcId ID for CV collection calculation in the pool.
     * @return array
     */
    private function collectVolumesAndLinks($poolCalcId)
    {
        /* load DB data */
        $query = $this->qCollectCv->build();
        $bind = [QCollectCv::BND_POOL_CALC_ID => $poolCalcId];
        $query->setParameters($bind);
        $stmt = $query->execute();
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, QCollectCv::RESULT_CLASS);
        /* create result maps */
        $mapLinks = [];
        $mapValues = [];
        /** @var DItem $one */
        foreach ($all as $one) {
            $regId = $one->cv_reg_id;
            $clientId = $one->client_id;
            $isAutoship = (bool)$one->is_autoship;
            $volume = $one->volume;
            /* compose CV movements map: [clientId => [cvMoveRegId, ...]] */
            if (!isset($mapLinks[$clientId])) {
                $mapLinks[$clientId] = [];
            }
            $mapLinks[$clientId][] = $regId;
            /* compose CV values map: [clientId][isAuto]=>CvTotal */
            if (!isset($mapValues[$clientId][$isAutoship])) {
                $mapValues[$clientId][$isAutoship] = 0;
            }
            $mapValues[$clientId][$isAutoship] += $volume;
        }
        return [$mapValues, $mapLinks];
    }

    /**
     * Group (A)PV for given tree.
     *
     * @param ARequest $req
     * @return AResponse
     */
    public function exec($req)
    {
        assert($req instanceof ARequest);
        $poolCalcIdOwn = $req->poolCalcIdOwn;
        $poolCalcIdCollect = $req->poolCalcIdCollect;
        $poolCalcIdTree = $req->poolCalcIdTree;

        /* get CV collected for the pool */
        [$volumes, $links] = $this->collectVolumesAndLinks($poolCalcIdCollect);
        $tree = $this->getTree($poolCalcIdTree);
        $this->savePvAndLinks($tree, $volumes, $links);

        $result = new AResponse();
        return $result;
    }

    private function getTree($poolCalcIdTree)
    {
        $bndCalcId = 'poolCalcId';
        $bind = [$bndCalcId => $poolCalcIdTree];
        $where = ETree::POOL_CALC_REF . "=:$bndCalcId";
        $result = $this->dao->getSet(ETree::class, $bind, $where);
        return $result;
    }

    /**
     * @param ETree[] $tree
     * @param array $cv [clientId][isAutoship]=>cv
     * @param array $links [clientId => [cvMoveRegId, ...]]
     */
    private function savePvAndLinks($tree, $cv, $links)
    {
        /* TODO: add CV movement from customers to distributors */
        if (is_array($tree)) {
            foreach ($tree as $one) {
                $treeNodeId = $one->id;
                $clientId = $one->client_ref;
                $pv = (isset($cv[$clientId][false])) ? $cv[$clientId][false] : 0;
                $apv = (isset($cv[$clientId][true])) ? $cv[$clientId][true] : 0;
                if ($pv || $apv) {
                    $ePv = new ETreePv();
                    $ePv->tree_node_ref = $treeNodeId;
                    $ePv->apv = $apv;
                    $ePv->pv = ($apv + $pv);
                    $this->dao->create($ePv);
                    $items = $links[$clientId];
                    foreach ($items as $item) {
                        $eLink = new ETreePvLink();
                        $eLink->tree_node_ref = $treeNodeId;
                        $eLink->cv_reg_ref = $item;
                        $this->dao->create($eLink);
                    }
                }
            }
        }
    }

    /**
     * @param int $poolCalcId
     * @param $reg [clientId][cvRegId, ...]
     */
    private function saveQuants($poolCalcId, $reg)
    {
        if (is_array($reg)) {
            foreach ($reg as $clientId => $refs) {
                foreach ($refs as $ref) {
                    $entity = new EResTreeQuant();
                    $entity->pool_calc_ref = $poolCalcId;
                    $entity->cv_reg_ref = $ref;
                    $entity->client_ref = $clientId;
                    $this->dao->create($entity);
                }
            }
        }
    }

}