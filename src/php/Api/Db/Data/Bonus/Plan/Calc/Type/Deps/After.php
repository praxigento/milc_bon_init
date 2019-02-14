<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type\Deps;


/**
 * Calculations are based on other calculations (ref depends on other_ref).
 *
 * @Entity
 * @Table(name="bon_plan_calc_type_deps_after")
 */
class After
    extends \TeqFw\Lib\Data
{
    const OTHER_REF = 'other_ref';
    const REF = 'ref';

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $other_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $ref;

}