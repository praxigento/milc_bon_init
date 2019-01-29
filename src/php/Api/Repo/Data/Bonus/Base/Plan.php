<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base;


/**
 * Set of calculations related to one period.
 *
 * @Entity
 * @Table(name="bon_base_plan")
 */
class Plan
    extends \TeqFw\Lib\Data
{
    const ID = 'id';
    const NOTE = 'note';
    const PERIOD = 'period';
    const DATE_CREATED = 'date_created';

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
     * @var \DateTime
     * @Column(type="date")
     */
    public $date_created;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $period;
}