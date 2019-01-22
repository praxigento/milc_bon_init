<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Customer;


/**
 * @Entity
 * @Table(name="dwn_cust_reg")
 */
class Registry
    extends \TeqFw\Lib\Data
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $customer_ref;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    public $is_deleted;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    public $is_inactive;
    /**
     * @var string
     * @Column(type="string")
     */
    public $mlm_id;

}