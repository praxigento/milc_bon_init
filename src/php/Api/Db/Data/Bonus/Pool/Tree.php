<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool;


/**
 * Downline tree data bound to bonus calculations.
 *
 * @Entity
 * @Table(name="bon_pool_tree")
 */
class Tree
    extends \TeqFw\Lib\Data
{
    public const CLIENT_REF = 'client_ref';
    public const ID = 'id';
    public const PARENT_REF = 'parent_ref';
    public const POOL_CALC_REF = 'pool_calc_ref';

    /**
     * @var int
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $id;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $parent_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $pool_calc_ref;
}