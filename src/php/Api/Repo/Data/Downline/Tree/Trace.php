<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree;


/**
 * @Entity
 * @Table(name="dwn_tree_trace")
 */
class Trace
    extends \TeqFw\Lib\Data
{
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
    public $member_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $parent_ref;

}