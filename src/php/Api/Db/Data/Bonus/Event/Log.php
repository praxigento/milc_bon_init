<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event;


/**
 * Log for bonus related events.
 *
 * @Entity
 * @Table(name="bon_event_log")
 */
class Log
    extends \TeqFw\Lib\Data
{
    const DATE = 'date';
    const ID = 'id';
    const TYPE = 'type';
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    public $date;
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
    public $type;
}