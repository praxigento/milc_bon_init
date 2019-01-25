<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Plan;


/**
 * Calculations are bound to the concrete plan.
 *
 * @Entity
 * @Table(name="bon_base_plan_calc")
 */
class Calc
    extends \TeqFw\Lib\Data
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    public $calc_id;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $plan_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $sequence;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $type_ref;
}