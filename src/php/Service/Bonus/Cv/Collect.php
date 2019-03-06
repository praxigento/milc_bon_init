<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Cv;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Cv as EPoolCv;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\A\Data\Movement as DMove;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\A\Db\Query\GetMovements as QGetMoves;
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

    private function collectMovements($dateFrom, $dateTo)
    {
        $query = $this->qGetMovements->build();
        $bind = [
            QGetMoves::BND_DATE_FROM => $dateFrom,
            QGetMoves::BND_DATE_TO => $dateTo
        ];
        $query->setParameters($bind);
        $stmt = $query->execute();
        $result = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, QGetMoves::RESULT_CLASS);
        return $result;
    }

    /**
     * Exclude negative and related positive (if exist) CV movements.
     *
     * @param DMove[] $items
     * @return DMove[]
     */
    private function excludeNegatives($items)
    {
        $result = [];
        /** @var int[] $mapBySaleId registry ID by sale ID */
        $mapBySaleId = [];
        foreach ($items as $item) {
            $regId = $item->reg_id;
            $saleId = $item->sale_id;
            $backSaleId = $item->back_id;
            if (is_null($backSaleId)) {
                $result[$regId] = $item;
                if ($saleId)
                    $mapBySaleId[$saleId] = $regId;
            } else {
                /* reset returned CV movement (if exists) */
                if (isset($mapBySaleId[$backSaleId])) {
                    $regIdBack = $mapBySaleId[$backSaleId];
                    unset($result[$regIdBack]);
                }
            }
        }
        return $result;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $poolCalcId = $req->poolCalcIdOwn;
        $dateFrom = $req->dateFrom;
        $dateTo = $req->dateTo;

        /** @var DMove[] $movements */
        $movements = $this->collectMovements($dateFrom, $dateTo);
        /* exclude backward movements and related forward movements */
        $positives = $this->excludeNegatives($movements);
        $this->saveItems($poolCalcId, $positives);
        $result = new AResponse();
        return $result;
    }

    /**
     * @param int $poolCalcId
     * @param DMove[] $items
     */
    private function saveItems($poolCalcId, $items)
    {
        foreach ($items as $item) {
            $entity = new EPoolCv();
            $entity->pool_calc_ref = $poolCalcId;
            $entity->cv_reg_ref = $item->reg_id;
            $this->dao->create($entity);
        }
    }
}