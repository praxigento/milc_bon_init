<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var int */
    public $clientId;
    /** @var string */
    public $date;
    /** @var int */
    public $parentIdNew;
}