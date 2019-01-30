<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv;


class Response
    extends \TeqFw\Lib\Data
{
    /** @var int ID of the newly created registry record. */
    public $registryId;
    /** @var string */
    public $sourceId;
    /** @var string */
    public $sourceType;
}