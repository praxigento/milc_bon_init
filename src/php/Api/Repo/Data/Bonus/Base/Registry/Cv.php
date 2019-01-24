<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Registry;


/**
 * Registry to save CV/ACV movements.
 *
 * @Entity
 * @Table(name="bon_base_reg_cv")
 */
class Cv
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
     * @Column(type="boolean")
     */
    public $is_autoship;
    /**
     * @var string
     * @Column(type="string")
     */
    public $note;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $volume;
}