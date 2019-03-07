<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl;


/**
 * Log to save 'delete/restore' events in clients registry.
 *
 * @Entity
 * @Table(name="bon_event_log_dwnl_del")
 */
class Delete
    extends \TeqFw\Lib\Data
{
    const CLIENT_REF = 'client_ref';
    const IS_DELETED = 'is_deleted';
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
    public $is_deleted;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $log_ref;
}