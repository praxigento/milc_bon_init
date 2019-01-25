<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Qualification;


/**
 * Set of calculations related to one period.
 *
 * @Entity
 * @Table(name="bon_base_qual_rule")
 */
class Rule
    extends \TeqFw\Lib\Data
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    public $id;
    /**
     * @var string
     * @Column(type="string")
     */
    public $type;
}