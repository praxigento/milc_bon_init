<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result;


/**
 * Qualifications on rank for calculation instances.
 *
 * @Entity
 * @Table(name="bon_res_rank")
 */
class Rank
    extends \TeqFw\Lib\Data
{
    const CLIENT_REF = 'client_ref';
    const POOL_CALC_REF = 'pool_calc_ref';
    const RANK_REF = 'rank_ref';
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $pool_calc_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $rank_ref;
}