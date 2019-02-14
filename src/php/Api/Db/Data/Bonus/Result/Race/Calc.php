<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race;


/**
 * Bonus calculation inside of period.
 *
 * @Entity
 * @Table(name="bon_res_race_calc")
 */
class Calc
    extends \TeqFw\Lib\Data
{
    const CALC_REF = 'calc_ref';
    const ID = 'id';
    const RACE_REF = 'race_ref';
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
    public $race_ref;
}