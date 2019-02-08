<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased;

class Response
    extends \TeqFw\Lib\Data
{
    /** @var \Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Period\Level[] */
    public $commissions;
}