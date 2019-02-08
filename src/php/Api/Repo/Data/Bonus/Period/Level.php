<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Period;


/**
 * Level based bonus commission values for period calculation.
 *
 * @Entity
 * @Table(name="bon_period_level")
 */
class Level
    extends \TeqFw\Lib\Data
{
    public const CALC_INST_REF = 'calc_inst_ref';
    public const CLIENT_REF = 'client_ref';
    public const COMMISSION = 'commission';
    public const CV = 'cv';
    public const LEVEL = 'level';
    public const PERCENT = 'percent';
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $calc_inst_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $commission;
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $cv;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $level;
    /**
     * @var float
     * @Column(type="decimal", precision=5, scale=4)
     */
    public $percent;
}