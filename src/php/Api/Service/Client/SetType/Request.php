<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client\SetType;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var int */
    public $customerId;
    /** @var string */
    public $date;
    /** @var bool */
    public $isCustomer;
}