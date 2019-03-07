<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv;

class Request
    extends \TeqFw\Lib\Data
{
    /** @var string including (>=$dateFrom) */
    public $dateFrom;
    /** @var string excluding (<$dateTo) */
    public $dateTo;
    /** @var int */
    public $poolCalcIdOwn;
}