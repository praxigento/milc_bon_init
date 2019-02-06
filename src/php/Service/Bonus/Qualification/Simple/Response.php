<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple;

use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Qualification\Rank as EQualRank;

class Response
    extends \TeqFw\Lib\Data
{
    /** @var EQualRank[] */
    public $entries;
}