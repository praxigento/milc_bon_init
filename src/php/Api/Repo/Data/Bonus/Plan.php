<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus;


/**
 * Set of suites related to one set of ranks.
 *
 * @Entity
 * @Table(name="bon_plan")
 */
class Plan
    extends \TeqFw\Lib\Data
{
    const DATE_CREATED = 'date_created';
    const ID = 'id';
    const NOTE = 'note';
    /**
     * @var \DateTime
     * @Column(type="date")
     */
    public $date_created;
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
}