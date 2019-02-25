<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool;


/**
 * Bonus periods.
 *
 * @Entity
 * @Table(name="bon_pool_period")
 */
class Period
    extends \TeqFw\Lib\Data
{
    const DATE_BEGIN = 'date_begin';
    const ID = 'id';
    const STATE = 'state';
    const SUITE_REF = 'suite_ref';

    /**
     * @var \DateTime
     * @Column(type="date")
     */
    public $date_begin;
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
    public $state;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $suite_ref;
}