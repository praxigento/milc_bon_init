<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Res;

/**
 * Ghost object from Flectra.
 *
 * @Entity
 * @Table(name="res_partner")
 */
class Partner
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