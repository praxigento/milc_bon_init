<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Comm;


/**
 * Level based bonus commission values for period calculation.
 *
 * @Entity
 * @Table(name="bon_pool_comm_level")
 */
class Level
    extends \TeqFw\Lib\Data
{
    public const CLIENT_REF = 'client_ref';
    public const COMMISSION = 'commission';
    public const CV = 'cv';
    public const ID = 'id';
    public const LEVEL = 'level';
    public const PERCENT = 'percent';
    public const POOL_CALC_REF = 'pool_calc_ref';
    /**
     * @var int
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
     * @Id
     * @Column(type="integer")
     */
    public $id;
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
    /**
     * @var int
     * @Column(type="integer")
     */
    public $pool_calc_ref;
}