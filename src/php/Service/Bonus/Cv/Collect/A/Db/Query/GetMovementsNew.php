<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\A\Db\Query;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry as ECvReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry\Sale as ECvRegSale;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry\Sale\Back as ECvRegSaleBack;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Cv as EPoolCv;
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\A\Data\Movement as DMove;

/**
 * Get all CV movements for given dates range (with movement type info).
 */
class GetMovementsNew
{
    private const AS_BACK = 'back';
    private const AS_REG = 'reg';
    private const AS_SALE = 'sale';

    private const BND_DATE_FROM = 'dateFrom';
    private const BND_DATE_TO = 'dateTo';

    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn
    ) {
        $this->conn = $conn;
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     * @return DMove[]
     */
    public function exec($dateFrom, $dateTo)
    {

        $asReg = self::AS_REG;
        $asSale = self::AS_SALE;
        $asBack = self::AS_BACK;
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_CV_REG, $asReg);
        $cols = [
            "$asReg." . ECvReg::ID . ' as ' . DMove::REG_ID,
            "$asReg." . ECvReg::CLIENT_REF . ' as ' . DMove::CLIENT_ID,
            "$asReg." . ECvReg::DATE . ' as ' . DMove::DATE,
            "$asReg." . ECvReg::IS_AUTOSHIP . ' as ' . DMove::IS_AUTOSHIP,
            "$asReg." . ECvReg::TYPE . ' as ' . DMove::TYPE,
            "$asReg." . ECvReg::VOLUME . ' as ' . DMove::VOLUME
        ];
        $qb->select($cols);

        /* LEFT JOIN bon_cv_reg_sale */
        $on = "$asSale." . ECvRegSale::REGISTRY_REF . "=$asReg." . ECvReg::ID;
        $qb->leftJoin($asReg, Cfg::DB_TBL_BON_CV_REG_SALE, $asSale, $on);
        $cols = [
            "$asSale." . ECvRegSale::SOURCE_REF . ' as ' . DMove::SALE_ID
        ];
        $qb->addSelect($cols);

        /* LEFT JOIN bon_cv_reg_sale_back */
        $on = "$asBack." . ECvRegSaleBack::REGISTRY_REF . "=$asReg." . ECvReg::ID;
        $qb->leftJoin($asReg, Cfg::DB_TBL_BON_CV_REG_SALE_BACK, $asBack, $on);
        $cols = [
            "$asBack." . ECvRegSaleBack::SOURCE_REF . ' as ' . DMove::BACK_ID
        ];
        $qb->addSelect($cols);

        /* WHERE */
        $byDateFrom = ECvReg::DATE . '>=:' . self::BND_DATE_FROM;
        $byDateTo = ECvReg::DATE . '<:' . self::BND_DATE_TO;
        $qb->where("($byDateFrom) AND ($byDateTo)");
        $qb->setParameters([
            self::BND_DATE_FROM => $dateFrom,
            self::BND_DATE_TO => $dateTo
        ]);

        $stmt = $qb->execute();
        /** @var EPoolCv[] $all */
        $result = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, DMove::class);
        return $result;
    }

}