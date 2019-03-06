<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool;

/**
 * Registry for (A)CV movements are selected for pool calculations.
 *
 * @Entity
 * @Table(name="bon_pool_cv")
 */
class Cv
    extends \TeqFw\Lib\Data
{
    const CV_REG_REF = 'cv_reg_ref';
    const POOL_CALC_REF = 'pool_calc_ref';
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