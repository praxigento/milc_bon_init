<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv;


/**
 * Registry to save CV/ACV movements.
 *
 * @Entity
 * @Table(name="bon_cv_reg")
 */
class Registry
    extends \TeqFw\Lib\Data
{
    const CLIENT_REF = 'client_ref';
    const DATE = 'date';
    const ID = 'id';
    const IS_AUTOSHIP = 'is_autoship';
    const TYPE = 'type';
    const VOLUME = 'volume';

    /**
     * @var int
     * @Column(type="integer")
     */
    public $client_ref;
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
     * @var bool
     * @Column(type="boolean")
     */
    public $is_autoship;
    /**
     * @var string
     * @Column(type="string")
     */
    public $type;
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $volume;
}