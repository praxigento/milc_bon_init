<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc;


/**
 * Codifier for calculation types.
 *
 * @Entity
 * @Table(name="bon_plan_calc_type")
 */
class Type
    extends \TeqFw\Lib\Data
{
    const CODE = 'code';
    const ID = 'id';
    const NOTE = 'note';

    /**
     * @var string
     * @Column(type="string")
     */
    public $code;
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
}