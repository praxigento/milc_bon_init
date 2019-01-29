<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Suite;


/**
 * Calculations are bound to the concrete plan.
 *
 * @Entity
 * @Table(name="bon_suite_calc")
 */
class Calc
    extends \TeqFw\Lib\Data
{
    const DATE_CREATED = 'date_created';
    const ID = 'id';
    const SEQUENCE = 'sequence';
    const SUITE_REF = 'suite_ref';
    const TYPE_REF = 'type_ref';

    /**
     * @var \DateTime
     * @Column(type="datetime")
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
     * @var int
     * @Column(type="integer")
     */
    public $sequence;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $suite_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $type_ref;
}