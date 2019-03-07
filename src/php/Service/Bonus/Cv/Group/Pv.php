<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Cv\Group;

use Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\Response as AResponse;

/**
 * Group (A)PV for given tree.
 */
class Pv
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao
    ) {
        $this->dao = $dao;
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
        $poolCalcId = $req->poolCalcIdOwn;
        $dateFrom = $req->dateFrom;
        $dateTo = $req->dateTo;


        $result = new AResponse();
        return $result;
    }

}