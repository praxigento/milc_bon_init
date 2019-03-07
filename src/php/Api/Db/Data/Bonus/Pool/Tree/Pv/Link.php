<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree\Pv;


/**
 * Links to the CV movements aggregated to PV.
 *
 * @Entity
 * @Table(name="bon_pool_tree_pv_link")
 */
class Link
    extends \TeqFw\Lib\Data
{
    public const CV_REG_REF = 'cv_reg_ref';
    public const TREE_NODE_REF = 'tree_node_ref';

    /**
     * @var int
     * @Column(type="integer")
     */
    public $cv_reg_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $tree_node_ref;
}