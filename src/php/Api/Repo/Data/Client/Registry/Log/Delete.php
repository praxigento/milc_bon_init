<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Client\Registry\Log;


/**
 * @Entity
 * @Table(name="client_reg_log_del")
 */
class Delete
    extends \TeqFw\Lib\Data
{
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
     * @Column(type="integer")
     * @GeneratedValue
     */
    public $id;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    public $is_deleted;
}