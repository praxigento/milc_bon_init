<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus;


/**
 * Downline tree data bound to bonus calculations.
 *
 * @Entity
 * @Table(name="bon_tree")
 */
class Tree
    extends \TeqFw\Lib\Data
{
    public const CALC_INST_REF = 'calc_inst_ref';
    public const CLIENT_REF = 'client_ref';
    public const PARENT_REF = 'parent_ref';
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $calc_inst_ref;
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
    public $parent_ref;
}