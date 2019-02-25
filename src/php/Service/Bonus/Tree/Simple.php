<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Tree;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Cv as EResCv;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Tree as EResTree;
use Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\Response as AResponse;

class Simple
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get */
    private $srvTreeGet;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get $srvTreeGet
    ) {
        $this->dao = $dao;
        $this->srvTreeGet = $srvTreeGet;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $raceCalcId = $req->raceCalcId;
        $raceCalcIdCvCollect = $req->raceCalcIdCvCollect;
        $dateTo = $req->dateTo;

        $cv = $this->getCvCollected($raceCalcIdCvCollect);
        $tree = $this->getDownlineTree($dateTo);
        $entries = $this->saveTree($raceCalcId, $tree, $cv);

        $result = new AResponse();
        return $result;
    }

    private function getCvCollected($raceCalcId)
    {
        $key = [EResCv::POOL_CALC_REF => $raceCalcId];
        $all = $this->dao->getSet(EResCv::class, $key);
        /* map CV by client/autoship */
        $result = [];
        /** @var EResCv $one */
        foreach ($all as $one) {
            $clientId = $one->client_ref;
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
     * @param int $raceCalcId
     * @param \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Min[] $tree
     * @param array $cv [clientId][isAutoship]=>cv
     * @return EResTree[]
     */
    private function saveTree($raceCalcId, $tree, $cv)
    {
        $result = [];
        if (is_array($tree)) {
            foreach ($tree as $one) {
                $clientId = $one->client_id;
                $pv = (isset($cv[$clientId][false])) ? $cv[$clientId][false] : 0;
                $apv = (isset($cv[$clientId][true])) ? $cv[$clientId][true] : 0;
                $entity = new EResTree();
                $entity->pool_calc_ref = $raceCalcId;
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