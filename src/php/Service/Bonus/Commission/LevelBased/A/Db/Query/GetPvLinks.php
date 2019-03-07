<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Db\Query;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry as ECvReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree\Pv\Link as EPvLink;
use Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Data\PvLinkEntry as DEntry;

class GetPvLinks
{
    public const AS_LINK = 'link';
    public const AS_REG = 'reg';
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
        $asLink = self::AS_LINK;
        $asReg = self::AS_REG;
        $asTree = self::AS_TREE;

        /** @var \Doctrine\DBAL\Query\QueryBuilder $result */
        $result = $this->conn->createQueryBuilder();

        /* FROM bon_pool_tree */
        $result->from(Cfg::DB_TBL_BON_POOL_TREE, $asTree);
        $cols = [];
        $result->select($cols);

        /* LEFT JOIN bon_pool_tree_pv_link */
        $on = "$asLink." . EPvLink::TREE_NODE_REF . "=$asTree." . ETree::ID;
        $result->leftJoin($asTree, Cfg::DB_TBL_BON_POOL_TREE_PV_LINK, $asLink, $on);
        $cols = [];
        $result->select($cols);

        /* LEFT JOIN bon_cv_reg */
        $on = "$asReg." . ECvReg::ID . "=$asLink." . EPvLink::CV_REG_REF;
        $result->leftJoin($asLink, Cfg::DB_TBL_BON_CV_REG, $asReg, $on);
        $cols = [
            "$asReg." . ECvReg::ID . ' as ' . DEntry::CV_REG_ID,
            "$asReg." . ECvReg::CLIENT_REF . ' as ' . DEntry::CLIENT_ID,
            "$asReg." . ECvReg::VOLUME . ' as ' . DEntry::VOLUME
        ];
        $result->addSelect($cols);

        /* WHERE */
        $byPoolCalcId = "$asTree." . ETree::POOL_CALC_REF . "=:" . self::BND_POOL_CALC_ID;
        $result->where($byPoolCalcId);

        return $result;
    }
}