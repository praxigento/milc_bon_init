<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Rule\Pv\A\Db\Query;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree\Pv as ETreePv;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Rule\Pv\A\Data\PvEntry as DEntry;

class GetTreePv
{
    public const AS_PV = 'pv';
    public const AS_TREE = 'tree';
    public const BND_POOL_CALC_ID = 'poolCalcId';
    public const RESULT_CLASS = DEntry::class;
    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Db */
    private $hlpDb;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn,
        \Praxigento\Milc\Bonus\Api\Helper\Db $hlpDb
    ) {
        $this->conn = $conn;
        $this->hlpDb = $hlpDb;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function build()
    {
        $asTree = self::AS_TREE;
        $asPv = self::AS_PV;

        /** @var \Doctrine\DBAL\Query\QueryBuilder $result */
        $result = $this->conn->createQueryBuilder();

        /* FROM bon_pool_tree */
        $result->from(Cfg::DB_TBL_BON_POOL_TREE, $asTree);
        $cols = [
            "$asTree." . ETree::ID . ' as ' . DEntry::NODE_ID,
            "$asTree." . ETree::CLIENT_REF . ' as ' . DEntry::CLIENT_ID
        ];
        $result->select($cols);

        /* LEFT JOIN bon_pool_tree_pv */
        $on = "$asPv." . ETreePv::TREE_NODE_REF . "=$asTree." . ETree::ID;
        $result->leftJoin($asTree, Cfg::DB_TBL_BON_POOL_TREE_PV, $asPv, $on);
        $cols = [
            "$asPv." . ETreePv::PV . ' as ' . DEntry::PV,
            "$asPv." . ETreePv::APV . ' as ' . DEntry::APV
        ];
        $result->addSelect($cols);

        /* WHERE */
        $byPoolCalcId = "$asTree." . ETree::POOL_CALC_REF . "=:" . self::BND_POOL_CALC_ID;
        $result->where($byPoolCalcId);

        return $result;
    }
}