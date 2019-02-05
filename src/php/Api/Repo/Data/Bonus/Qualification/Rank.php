<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Qualification;


/**
 * Qualifications on rank for calculation instances.
 *
 * @Entity
 * @Table(name="bon_qual_rank")
 */
class Rank
    extends \TeqFw\Lib\Data
{
    const CALC_INST_REF = 'calc_inst_ref';
    const CLIENT_REF = 'client_ref';
    const RANK_REF = 'rank_ref';

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $calc_inst_ref;
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
    public $rank_ref;
}