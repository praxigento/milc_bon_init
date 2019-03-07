<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Tree;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as EResTree;
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
        $poolCalcId = $req->poolCalcIdOwn;
        $dateTo = $req->dateTo;

        $tree = $this->getDownlineTree($dateTo);
        $this->saveTree($poolCalcId, $tree);

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
     * @param \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Min[] $tree
     */
    private function saveTree($poolCalcId, $tree)
    {
        if (is_array($tree)) {
            foreach ($tree as $one) {
                $entity = new EResTree();
                $entity->pool_calc_ref = $poolCalcId;
                $entity->client_ref = $one->client_id;
                $entity->parent_ref = $one->parent_id;
                $this->dao->create($entity);
            }
        }
    }
}