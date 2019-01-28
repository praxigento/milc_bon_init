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
    const DATE_STARTED= 'date_started';
    const ID = 'id';
    const PLAN_REF= 'plan_ref';
    const SEQUENCE= 'sequence';
    const TYPE_REF= 'type_ref';

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    public $date_started;
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    public $id;
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