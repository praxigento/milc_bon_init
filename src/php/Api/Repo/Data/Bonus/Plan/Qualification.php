<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Plan;


/**
 * Rank qualification rules for the calculation.
 *
 * @Entity
 * @Table(name="bon_plan_qual")
 */
class Qualification
    extends \TeqFw\Lib\Data
{
    const CALC_REF = 'calc_ref';
    const RANK_REF = 'rank_ref';
    const RULE_REF = 'rule_ref';

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $calc_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $rank_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $rule_ref;

}