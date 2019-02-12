<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan;


/**
 * Set of calculations related to one period.
 *
 * @Entity
 * @Table(name="bon_plan_suite")
 */
class Suite
    extends \TeqFw\Lib\Data
{
    const DATE_CREATED = 'date_created';
    const ID = 'id';
    const NOTE = 'note';
    const PLAN_REF = 'plan_ref';
    /**
     * @var \DateTime
     * @Column(type="date")
     */
    public $date_created;
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    public $id;
    /**
     * @var string
     * @Column(type="string")
     */
    public $note;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $plan_ref;
}