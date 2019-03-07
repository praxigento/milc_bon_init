<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree\Pv as ETreePv;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Data\PvEntry as DEntry;

class GetPv
{
    public const AS_PV = 'pv';
    public const AS_TREE = 'tree';
    public const BND_POOL_CALC_ID = 'poolCalcId';
    public const RESULT_CLASS = DEntry::class;

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
        $asPv = self::AS_PV;
        $asTree = self::AS_TREE;

        /** @var \Doctrine\DBAL\Query\QueryBuilder $result */
        $result = $this->conn->createQueryBuilder();

        /* FROM bon_pool_tree */
        $result->from(Cfg::DB_TBL_BON_POOL_TREE, $asTree);
        $cols = [
            "$asTree." . ETree::CLIENT_REF . ' as ' . DEntry::CLIENT_ID
        ];
        $result->select($cols);

        /* LEFT JOIN bon_pool_tree_pv */
        $on = "$asPv." . ETreePv::TREE_NODE_REF . "=$asTree." . ETree::ID;
        $result->leftJoin($asTree, Cfg::DB_TBL_BON_POOL_TREE_PV, $asPv, $on);
        $cols = [
            "$asPv." . ETreePv::APV . ' as ' . DEntry::APV,
            "$asPv." . ETreePv::PV . ' as ' . DEntry::PV
        ];
        $result->addSelect($cols);

        /* WHERE */
        $byPoolCalcId = "$asTree." . ETree::POOL_CALC_REF . "=:" . self::BND_POOL_CALC_ID;
        $result->where($byPoolCalcId);

        return $result;
    }
}