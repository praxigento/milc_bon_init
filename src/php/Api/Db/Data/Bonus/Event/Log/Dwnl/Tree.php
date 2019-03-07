<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl;


/**
 * Log to save changes in 'parent-child' relations between clients (tree states in retrospective).
 *
 * @Entity
 * @Table(name="bon_event_log_dwnl_tree")
 */
class Tree
    extends \TeqFw\Lib\Data
{
    const CLIENT_REF = 'client_ref';
    const LOG_REF = 'log_ref';
    const PARENT_REF = 'parent_ref';
    /**
     * @var int
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $log_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $parent_ref;
}