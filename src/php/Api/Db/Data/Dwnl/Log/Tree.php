<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Log;


/**
 * @Entity
 * @Table(name="dwnl_log_tree")
 */
class Tree
    extends \TeqFw\Lib\Data
{
    public const CLIENT_REF = 'client_ref';
    public const DATE = 'date';
    public const ID = 'id';
    public const PARENT_REF = 'parent_ref';
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
     * @var int
     * @Column(type="integer")
     */
    public $parent_ref;

}