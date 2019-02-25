<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Rank;


/**
 * Set of calculations related to one period.
 *
 * @Entity
 * @Table(name="bon_calc_rank_rule")
 */
class Rule
    extends \TeqFw\Lib\Data
{
    const ID = 'id';
    const TYPE = 'type';

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