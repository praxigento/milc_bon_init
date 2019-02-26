<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Tree;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as EResTree;
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

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $poolCalcId = $req->poolCalcIdOwn;
        $poolCalcIdCvCollect = $req->poolCalcIdCv;
        $dateTo = $req->dateTo;

        $cv = $this->getCvCollected($poolCalcIdCvCollect);
        $tree = $this->getDownlineTree($dateTo);
        $entries = $this->saveTree($poolCalcId, $tree, $cv);

        $result = new AResponse();
        return $result;
    }

    private function getCvCollected($poolCalcId)
    {
        $query = $this->qCollectCv->build();
        $bind = [QCollectCv::BND_POOL_CALC_ID => $poolCalcId];
        $query->setParameters($bind);
        $stmt = $query->execute();
        $all = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, QCollectCv::RESULT_CLASS);
        /* map CV by client/autoship */
        $result = [];
        /** @var DItem $one */
        foreach ($all as $one) {
            $clientId = $one->client_id;
            $isAutoship = (bool)$one->is_autoship;
            $result[$clientId][$isAutoship] = $one->volume;
        }
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
     * @param \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Min[] $tree
     * @param array $cv [clientId][isAutoship]=>cv
     * @return EResTree[]
     */
    private function saveTree($poolCalcId, $tree, $cv)
    {
        $result = [];
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
        return $result;
    }
}