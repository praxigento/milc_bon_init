<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var int ID of the calculation in the period. */
    public $calcInstId;
    /** @var \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Period\Tree[] */
    public $tree;
}