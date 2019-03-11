<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl;


/**
 * Tree to save current 'parent-child' relations between clients.
 *
 * @Entity
 * @Table(name="bon_dwnl_tree")
 */
class Tree
    extends \TeqFw\Lib\Data
{
    const CLIENT_REF = 'client_ref';
    const DEPTH = 'depth';
    const PARENT_REF = 'parent_ref';
    const PATH = 'path';
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $depth;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $parent_ref;
    /**
     * @var string
     * @Column(type="string")
     */
    public $path;
}