<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry;

/**
 * Tree entry with full data (indexed).
 */
class Full
    extends \TeqFw\Lib\Data
{
    /** @var int */
    public $client_id;
    /** @var int */
    public $depth;
    /** @var int */
    public $parent_id;
    /**
     * ":1:21:324:543:" - path to the current node including parent id
     * (@see \Praxigento\Milc\Bonus\Api\Config::TREE_PS)
     *
     * @var string
     */
    public $path;
}