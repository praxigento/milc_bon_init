<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client\Add\A\Helper\TreeBinary\A\Db\Query;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl\Tree\Bin as ETreeBin;
use Praxigento\Milc\Bonus\Service\Client\Add\A\Helper\TreeBinary\A\Data\Entry as DEntry;

class GetChildren
{
    public const AS_BIN = 'bin';
    public const AS_TREE = 'tree';
    public const BND_CLIENT_ID = 'clientId';
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
        $asBin = self::AS_BIN;
        $asTree = self::AS_TREE;

        /** @var \Doctrine\DBAL\Query\QueryBuilder $result */
        $result = $this->conn->createQueryBuilder();

        /* FROM bon_dwnl_tree */
        $result->from(Cfg::DB_TBL_BON_DWNL_TREE, $asTree);
        $cols = [
            "$asTree." . ETree::CLIENT_REF . ' as ' . DEntry::CLIENT_ID,
            "$asTree." . ETree::PARENT_REF . ' as ' . DEntry::PARENT_ID
        ];
        $result->select($cols);

        /* LEFT JOIN bon_dwnl_tree_bin */
        $on = "$asBin." . ETreeBin::CLIENT_REF . "=$asTree." . ETree::CLIENT_REF;
        $result->leftJoin($asTree, Cfg::DB_TBL_BON_DWNL_TREE_BIN, $asBin, $on);
        $cols = [
            "$asBin." . ETreeBin::IS_ON_LEFT . ' as ' . DEntry::IS_LEFT
        ];
        $result->addSelect($cols);

        /* WHERE */
        $byParentId = "$asTree." . ETree::PARENT_REF . "=:" . self::BND_CLIENT_ID;
        $byNotRoot = "$asTree." . ETree::CLIENT_REF . "!=" . ETree::PARENT_REF;
        $where = "($byParentId) AND ($byNotRoot)";
        $result->where($where);

        return $result;
    }
}