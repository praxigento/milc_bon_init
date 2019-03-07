<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl;


/**
 * Log to save 'setType' changes in clients registry.
 *
 * @Entity
 * @Table(name="bon_event_log_dwnl_type")
 */
class Type
    extends \TeqFw\Lib\Data
{
    const CLIENT_REF = 'client_ref';
    const IS_CUSTOMER = 'is_customer';
    const LOG_REF = 'log_ref';
    /**
     * @var int
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    public $is_customer;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $log_ref;
}