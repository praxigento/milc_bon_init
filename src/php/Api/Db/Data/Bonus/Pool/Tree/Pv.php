<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree;


/**
 * PV for downline tree customers.
 *
 * @Entity
 * @Table(name="bon_pool_tree_pv")
 */
class Pv
    extends \TeqFw\Lib\Data
{
    public const APV = 'apv';
    public const PV = 'pv';
    public const TREE_NODE_REF = 'tree_node_ref';
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $apv;
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $pv;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $tree_node_ref;
}