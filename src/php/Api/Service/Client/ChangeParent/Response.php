<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent;


class Response
    extends \TeqFw\Lib\Data
{
    /** @var int */
    public $parentIdOld;
    /** @var bool */
    public $success;
}