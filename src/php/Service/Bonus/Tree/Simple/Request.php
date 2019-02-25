<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple;

class Request
    extends \TeqFw\Lib\Data
{
    /** @var string excluding (<$dateTo) */
    public $dateTo;
    /** @var int */
    public $poolCalcId;
    /** @var int */
    public $poolCalcIdCvCollect;
}