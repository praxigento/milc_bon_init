<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result;


/**
 * Downline tree data bound to bonus calculations.
 *
 * @Entity
 * @Table(name="bon_res_tree")
 */
class Tree
    extends \TeqFw\Lib\Data
{
    public const APV = 'apv';
    public const CALC_INST_REF = 'calc_inst_ref';
    public const CLIENT_REF = 'client_ref';
    public const PARENT_REF = 'parent_ref';
    public const PV = 'pv';
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $apv;
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
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $pv;
}