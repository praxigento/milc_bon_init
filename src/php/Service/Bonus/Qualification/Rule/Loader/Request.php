<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Rule\Loader;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var int[] IDs of the root rules. */
    public $rootIds;
}