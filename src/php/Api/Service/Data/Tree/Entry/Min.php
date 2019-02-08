<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry;

/**
 * Tree entry with required data only.
 */
class Min
    extends \TeqFw\Lib\Data
{
    /** @var int */
    public $client_id;
    /** @var int */
    public $parent_id;
}