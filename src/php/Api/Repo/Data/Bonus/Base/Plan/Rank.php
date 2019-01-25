<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Plan;


/**
 * Ranks are bound to the concrete plan.
 *
 * @Entity
 * @Table(name="bon_base_plan_rank")
 */
class Rank
    extends \TeqFw\Lib\Data
{
    /**
     * @var string
     * @Column(type="string")
     */
    public $code;
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
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    public $rank_id;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $sequence;
}