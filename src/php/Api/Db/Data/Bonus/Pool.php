<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus;


/**
 * Instances of the one set of calculations from one suite inside one period (cancelled, forecast, complete, etc).
 *
 * @Entity
 * @Table(name="bon_pool")
 */
class Pool
    extends \TeqFw\Lib\Data
{
    const DATE_STARTED = 'date_started';
    const ID = 'id';
    const PERIOD_REF = 'period_ref';

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    public $date_started;
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    public $id;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $period_ref;
}