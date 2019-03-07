<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree;


/**
 * Organization volume for downline tree customers.
 *
 * @Entity
 * @Table(name="bon_pool_tree_ov")
 */
class Ov
    extends \TeqFw\Lib\Data
{
    public const OV = 'ov';
    public const TREE_NODE_REF = 'tree_node_ref';

    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $ov;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $tree_node_ref;
}