<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Sale;

/**
 * Ghost object from Flectra.
 *
 * @Entity
 * @Table(name="sale_order")
 */
class Order
    extends \TeqFw\Lib\Data
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    public $id;
}