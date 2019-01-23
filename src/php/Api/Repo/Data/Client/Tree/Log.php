<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Client\Tree;


/**
 * @Entity
 * @Table(name="client_tree_log")
 */
class Log
    extends \TeqFw\Lib\Data
{
    /**
     * @var int
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var int
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