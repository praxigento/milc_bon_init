<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry as ECvReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree\Quant as ETreeQuant;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Data\TreeQuant as DItem;

class GetTreeQuants
{
    public const AS_QUANT = 'quant';
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
        $asQuant = self::AS_QUANT;
        $asReg = self::AS_REG;

        /** @var \Doctrine\DBAL\Query\QueryBuilder $result */
        $result = $this->conn->createQueryBuilder();

        /* FROM bon_pool_tree_quant */
        $result->from(Cfg::DB_TBL_BON_POOL_TREE_QUANT, $asQuant);
        $cols = [];
        $result->select($cols);

        /* LEFT JOIN bon_cv_reg */
        $on = "$asReg." . ECvReg::ID . "=$asQuant." . ETreeQuant::CV_REG_REF;
        $result->leftJoin($asQuant, Cfg::DB_TBL_BON_CV_REG, $asReg, $on);
        $cols = [
            "$asReg." . ECvReg::ID . ' as ' . DItem::CV_REG_ID,
            "$asReg." . ECvReg::CLIENT_REF . ' as ' . DItem::CLIENT_ID,
            "$asReg." . ECvReg::VOLUME . ' as ' . DItem::VOLUME
        ];
        $result->addSelect($cols);

        /* WHERE */
        $byPoolCalcId = "$asQuant." . ETreeQuant::POOL_CALC_REF . "=:" . self::BND_POOL_CALC_ID;
        $result->where($byPoolCalcId);

        return $result;
    }
}