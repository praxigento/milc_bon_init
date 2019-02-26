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
use Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\A\Data\Movement as DMove;

/**
 * Get all CV movements for given dates range (with movement type info).
 */
class GetMovements
{
    public const AS_BACK = 'back';
    public const AS_REG = 'reg';
    public const AS_SALE = 'sale';

    public const BND_DATE_FROM = 'dateFrom';
    public const BND_DATE_TO = 'dateTo';

    public const RESULT_CLASS = DMove::class;

    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn
    ) {
        $this->conn = $conn;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function build()
    {

        $asReg = self::AS_REG;
        $asSale = self::AS_SALE;
        $asBack = self::AS_BACK;
        /** @var \Doctrine\DBAL\Query\QueryBuilder $result */
        $result = $this->conn->createQueryBuilder();
        $result->from(Cfg::DB_TBL_BON_CV_REG, $asReg);
        $cols = [
            "$asReg." . ECvReg::ID . ' as ' . DMove::REG_ID
        ];
        $result->select($cols);

        /* LEFT JOIN bon_cv_reg_sale */
        $on = "$asSale." . ECvRegSale::REGISTRY_REF . "=$asReg." . ECvReg::ID;
        $result->leftJoin($asReg, Cfg::DB_TBL_BON_CV_REG_SALE, $asSale, $on);
        $cols = [
            "$asSale." . ECvRegSale::SOURCE_REF . ' as ' . DMove::SALE_ID
        ];
        $result->addSelect($cols);

        /* LEFT JOIN bon_cv_reg_sale_back */
        $on = "$asBack." . ECvRegSaleBack::REGISTRY_REF . "=$asReg." . ECvReg::ID;
        $result->leftJoin($asReg, Cfg::DB_TBL_BON_CV_REG_SALE_BACK, $asBack, $on);
        $cols = [
            "$asBack." . ECvRegSaleBack::SOURCE_REF . ' as ' . DMove::BACK_ID
        ];
        $result->addSelect($cols);

        /* WHERE */
        $byDateFrom = ECvReg::DATE . '>=:' . self::BND_DATE_FROM;
        $byDateTo = ECvReg::DATE . '<:' . self::BND_DATE_TO;
        $result->where("($byDateFrom) AND ($byDateTo)");

        return $result;
    }

}