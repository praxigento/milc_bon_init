<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Tree;


/**
 * Links to the CV movements aggregated to PV.
 *
 * @Entity
 * @Table(name="bon_pool_tree_quant")
 */
class Quant
    extends \TeqFw\Lib\Data
{
    public const CLIENT_REF = 'client_ref';
    public const CV_REG_REF = 'cv_reg_ref';
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
    public $cv_reg_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $pool_calc_ref;
}