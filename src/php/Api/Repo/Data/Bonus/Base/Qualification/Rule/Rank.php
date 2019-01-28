<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Qualification\Rule;


/**
 * Rank based rules.
 *
 * @Entity
 * @Table(name="bon_base_qual_rule_rank")
 */
class Rank
    extends \TeqFw\Lib\Data
{
    const COUNT = 'count';
    const PERIOD = 'period';
    const RANK_REF = 'rank_ref';
    const REF = 'ref';

    /**
     * @var int
     * @Column(type="integer")
     */
    public $count;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $period;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $rank_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $ref;

}