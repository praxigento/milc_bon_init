<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\A\Db\Query;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry as ECvReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Cv\Item as EPoolCvItem;
use Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\A\Data\Item as DItem;

/**
 * Create query to collect CV quants to calculate PV/GV/...
 */
class CollectCv
{
    public const AS_ITEM = 'item';
    public const AS_REG = 'reg';
    public const BND_POOL_CALC_ID = 'poolCalcId';
    public const RESULT_CLASS = DItem::class;

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
        $asItem = self::AS_ITEM;
        $asReg = self::AS_REG;

        /** @var \Doctrine\DBAL\Query\QueryBuilder $result */
        $result = $this->conn->createQueryBuilder();
        $result->from(Cfg::DB_TBL_BON_POOL_CV_ITEM, $asItem);
        $cols = [];
        $result->select($cols);

        /* LEFT JOIN bon_cv_reg */
        $on = "$asReg." . ECvReg::ID . "=$asItem." . EPoolCvItem::CV_REG_REF;
        $result->leftJoin($asItem, Cfg::DB_TBL_BON_CV_REG, $asReg, $on);
        $cols = [
            "$asReg." . ECvReg::ID . ' as ' . DItem::CV_REG_ID,
            "$asReg." . ECvReg::CLIENT_REF . ' as ' . DItem::CLIENT_ID,
            "$asReg." . ECvReg::IS_AUTOSHIP . ' as ' . DItem::IS_AUTOSHIP,
            "$asReg." . ECvReg::VOLUME . ' as ' . DItem::VOLUME
        ];
        $result->addSelect($cols);

        /* WHERE */
        $byPoolCalcId = "$asItem." . EPoolCvItem::POOL_CALC_REF . "=:" . self::BND_POOL_CALC_ID;
        $result->where($byPoolCalcId);

        /* GROUP */
//        $result->groupBy("$asReg." . ECvReg::CLIENT_REF, "$asReg." . ECvReg::IS_AUTOSHIP);

        return $result;
    }
}