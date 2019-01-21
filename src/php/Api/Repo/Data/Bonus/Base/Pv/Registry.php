<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Pv;


/**
 * Registry to save PV/APV movements.
 *
 * @Entity
 * @Table(name="bon_base_pv_reg")
 */
class Registry
    extends \TeqFw\Lib\Data
{
    /**
     * @var int
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
     * @var bool
     * @Column(type="bool")
     */
    public $is_autoship;
    /**
     * @var string
     * @Column(type="string")
     */
    public $note;
    /**
     * @var float
     * @Column(type="decimal")
     */
    public $volume;
}