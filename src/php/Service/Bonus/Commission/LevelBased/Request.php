<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased;

class Request
    extends \TeqFw\Lib\Data
{
    /** @var int ID of the ranks qualification calculation in the period. */
    public $ranksCalcInstId;
    /** @var int ID of the calculation in the period. */
    public $thisCalcInstId;
    /** @var int ID of the tree calculation in the period (contains PV). */
    public $treeCalcInstId;
}