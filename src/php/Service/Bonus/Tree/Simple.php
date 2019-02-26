<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Tree;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as EResTree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree\Quant as EResTreeQuant;
use Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\A\Data\Item as DItem;
use Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\A\Db\Query\CollectCv as QCollectCv;
use Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\Response as AResponse;

class Simple
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\A\Db\Query\CollectCv */
    private $qCollectCv;
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get */
    private $srvTreeGet;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\A\Db\Query\CollectCv $qCollectCv,
        \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get $srvTreeGet
    ) {
        $this->dao = $dao;
        $this->qCollectCv = $qCollectCv;
        $this->srvTreeGet = $srvTreeGet;
    }

    private function collectCv($poolCalcId)
    {
        /* load DB data */
        $query = $this->qCollectCv->build();
        $bind = [QCollectCv::BND_POOL_CALC_ID => $poolCalcId];
        $query->setParameters($bind);
        $stmt = $query->execute();
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, QCollectCv::RESULT_CLASS);
        /* create result maps */
        $mapReg = [];
        $mapValue = [];
        /** @var DItem $one */
        foreach ($all as $one) {
            $regId = $one->cv_reg_id;
            $clientId = $one->client_id;
            $isAutoship = (bool)$one->is_autoship;
            $volume = $one->volume;
            /* compose CV movements map */
            if (!isset($mapReg[$clientId])) {
                $mapReg[$clientId] = [];
            }
            $mapReg[$clientId][] = $regId;
            /* compose CV values map */
            if (!isset($mapValue[$clientId][$isAutoship])) {
                $mapValue[$clientId][$isAutoship] = 0;
            }
            $mapValue[$clientId][$isAutoship] += $volume;
        }
        return [$mapReg, $mapValue];
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $poolCalcId = $req->poolCalcIdOwn;
        $poolCalcIdCv = $req->poolCalcIdCv;
        $dateTo = $req->dateTo;

        [$regs, $volumes] = $this->collectCv($poolCalcIdCv);
        $tree = $this->getDownlineTree($dateTo);
        $this->saveTree($poolCalcId, $tree, $volumes);
        $this->saveQuants($poolCalcId, $regs);

        $result = new AResponse();
        return $result;
    }

    /**
     * @param string $dateTo
     * @return \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Min[]
     */
    private function getDownlineTree($dateTo)
    {
        $req = new \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Request();
        $req->date = $dateTo;
        /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Response $resp */
        $resp = $this->srvTreeGet->exec($req);
        $result = $resp->entries;
        return $result;
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

    /**
     * @param int $poolCalcId
     * @param \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Min[] $tree
     * @param array $cv [clientId][isAutoship]=>cv
     */
    private function saveTree($poolCalcId, $tree, $cv)
    {
        if (is_array($tree)) {
            foreach ($tree as $one) {
                $clientId = $one->client_id;
                $pv = (isset($cv[$clientId][false])) ? $cv[$clientId][false] : 0;
                $apv = (isset($cv[$clientId][true])) ? $cv[$clientId][true] : 0;
                $entity = new EResTree();
                $entity->pool_calc_ref = $poolCalcId;
                $entity->client_ref = $one->client_id;
                $entity->parent_ref = $one->parent_id;
                $entity->apv = $apv;
                $entity->pv = ($apv + $pv);
                $this->dao->create($entity);
                $result[] = $entity;
            }
        }
    }
}