<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl\Tree;


/**
 * Additional details for binary trees.
 *
 * @Entity
 * @Table(name="bon_dwnl_tree_bin")
 */
class Bin
    extends \TeqFw\Lib\Data
{
    const CLIENT_REF = 'client_ref';
    const IS_ON_LEFT = 'is_on_left';
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var bool
     * @Column(type="boolean")
     */
    public $is_on_left;
}