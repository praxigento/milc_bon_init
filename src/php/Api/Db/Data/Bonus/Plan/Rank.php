<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan;


/**
 * Ranks are bound to the concrete plan.
 *
 * @Entity
 * @Table(name="bon_plan_rank")
 */
class Rank
    extends \TeqFw\Lib\Data
{
    const CODE = 'code';
    const ID = 'id';
    const NOTE = 'note';
    const PLAN_REF = 'plan_ref';
    const SEQUENCE = 'sequence';

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
}