<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Client;


/**
 * @Entity
 * @Table(name="client_reg")
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