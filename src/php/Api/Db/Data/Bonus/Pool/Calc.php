<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool;


/**
 * Bonus calculation inside of period.
 *
 * @Entity
 * @Table(name="bon_pool_calc")
 */
class Calc
    extends \TeqFw\Lib\Data
{
    const ID = 'id';
    const POOL_REF = 'pool_ref';
    const SUITE_CALC_REF = 'suite_calc_ref';
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
    public $pool_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $suite_calc_ref;
}