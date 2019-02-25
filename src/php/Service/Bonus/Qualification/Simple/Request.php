<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var int race calculation ID for qualification calculation. */
    public $poolCalcIdQual;
    /** @var int race calculation ID for tree composition calculation related to the current race qualification */
    public $poolCalcIdTree;
    /**
     * @var \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree[]
     * @deprecated use $poolCalcIdTree
     */
    public $tree;
}