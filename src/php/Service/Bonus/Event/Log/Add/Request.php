<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Event\Log\Add;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var \DateTime|string */
    public $date;
    /** @var mixed */
    public $details;
}