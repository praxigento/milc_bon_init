<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Comm;


/**
 * Configuration for level based commission calculations.
 *
 * @Entity
 * @Table(name="bon_calc_comm_level")
 */
class Level
    extends \TeqFw\Lib\Data
{
    const LEVEL = 'level';
    const PERCENT = 'percent';
    const RANK_REF = 'rank_ref';
    const SUITE_CALC_REF = 'suite_calc_ref';
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
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $suite_calc_ref;

}