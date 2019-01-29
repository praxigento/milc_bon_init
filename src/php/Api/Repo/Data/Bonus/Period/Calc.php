<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus;


/**
 * Bonus calculation inside of period.
 *
 * @Entity
 * @Table(name="bon_period_calc")
 */
class Calc
    extends \TeqFw\Lib\Data
{
    const CALC_REF = 'calc_ref';
    const ID = 'id';
    const PERIOD_REF = 'period_ref';
    /**
     * @var int
     * @Column(type="integer")
     */
    public $calc_ref;
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
    public $period_ref;
}