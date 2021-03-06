<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl;


/**
 * @Entity
 * @Table(name="bon_dwnl_reg")
 */
class Registry
    extends \TeqFw\Lib\Data
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $enroller_ref;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    public $is_customer;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    public $is_deleted;
    /**
     * @var string
     * @Column(type="string")
     */
    public $mlm_id;

}