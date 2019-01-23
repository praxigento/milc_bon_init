<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Client;


/**
 * @Entity
 * @Table(name="client_tree")
 */
class Tree
    extends \TeqFw\Lib\Data
{
    const DEPTH = 'depth';
    const CLIENT_REF = 'client_ref';
    const PARENT_REF = 'parent_ref';
    const PATH = 'path';

    /**
     * @var int
     * @Column(type="integer")
     */
    public $depth;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $parent_ref;
    /**
     * @var string
     * @Column(type="string")
     */
    public $path;
}