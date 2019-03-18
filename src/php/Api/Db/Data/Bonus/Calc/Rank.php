<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc;


/**
 * Configuration for ranks qualification rules.
 *
 * @Entity
 * @Table(name="bon_calc_rank")
 */
class Rank
    extends \TeqFw\Lib\Data
{
    const RANK_REF = 'rank_ref';
    const RULE_REF = 'rule_ref';
    const SUITE_CALC_REF = 'suite_calc_ref';
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
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $suite_calc_ref;

}