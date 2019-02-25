<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Cv;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Cv as EPoolCv;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\Response as AResponse;

/**
 * Collect CV for given period and save it with given poolCalcId.
 */
class Collect
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\A\Db\Query\GetMovements */
    private $qGetMovements;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\A\Db\Query\GetMovements $qGetMovements
    ) {
        $this->dao = $dao;
        $this->qGetMovements = $qGetMovements;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $poolCalcId = $req->poolCalcId;
        $dateFrom = $req->dateFrom;
        $dateTo = $req->dateTo;

        /** @var EPoolCv[] $movements */
        $movements = $this->qGetMovements->exec($dateFrom, $dateTo);
        foreach ($movements as $one) {
            $one->pool_calc_ref = $poolCalcId;
            $this->dao->create($one);
        }

        $result = new AResponse();
        return $result;
    }


}