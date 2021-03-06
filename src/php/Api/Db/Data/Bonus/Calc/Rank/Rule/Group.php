<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Rank\Rule;


/**
 * Grouping rules.
 *
 * @Entity
 * @Table(name="bon_calc_rank_rule_group")
 */
class Group
    extends \TeqFw\Lib\Data
{
    const LOGIC = 'logic';
    const REF = 'ref';
    /**
     * @var string
     * @Column(type="string")
     */
    public $logic;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $ref;
}