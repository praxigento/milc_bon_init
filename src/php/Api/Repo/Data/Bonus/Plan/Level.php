<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Plan;


/**
 * Configuration for level based commission calculations.
 *
 * @Entity
 * @Table(name="bon_plan_level")
 */
class Level
    extends \TeqFw\Lib\Data
{
    const CALC_REF = 'calc_ref';
    const LEVEL = 'level';
    const PERCENT = 'percent';
    const RANK_REF = 'rank_ref';
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
    public $level;
    /**
     * @var float
     * @Column(type="decimal", precision=5, scale=4)
     */
    public $percent;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $rank_ref;

}