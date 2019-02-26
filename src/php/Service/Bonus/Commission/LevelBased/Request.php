<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased;

class Request
    extends \TeqFw\Lib\Data
{
    /** @var int ID of the CV collection calculation in the pool. */
    public $poolCalcIdCv;
    /** @var int ID of this calculation in the pool. */
    public $poolCalcIdOwn;
    /** @var int ID of the ranks qualification calculation in the pool. */
    public $poolCalcIdRanks;
    /** @var int ID of the tree calculation in the period (contains aggregated PV). */
    public $poolCalcIdTree;
}