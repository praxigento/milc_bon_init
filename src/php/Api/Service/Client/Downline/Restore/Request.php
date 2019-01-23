<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client\Downline\Restore;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var int */
    public $customerId;
    /** @var string */
    public $date;
    /** @var int */
    public $parentId;
}