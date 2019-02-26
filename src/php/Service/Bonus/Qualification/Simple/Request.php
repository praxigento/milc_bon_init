<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var int calculation ID for qualification calculation in the pool. */
    public $poolCalcIdRank;
    /** @var int calculation ID for tree composition calculation in the pool (related to the current qualification) */
    public $poolCalcIdTree;
    /**
     * @var \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree[]
     * @deprecated use $poolCalcIdTree
     */
    public $tree;
}