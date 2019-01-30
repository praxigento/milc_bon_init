<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var int */
    public $clientId;
    /** @var string */
    public $date;
    /** @var bool */
    public $isAutoship;
    /** @var string */
    public $sourceId;
    /** @var string  sale, sale_return */
    public $sourceType;
    /** @var float */
    public $volume;
}