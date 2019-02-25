<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result;


/**
 * CV/ACV are collected for period.
 *
 * @Entity
 * @Table(name="bon_res_cv")
 */
class Cv
    extends \TeqFw\Lib\Data
{
    const CLIENT_REF = 'client_ref';
    const IS_AUTOSHIP = 'is_autoship';
    const POOL_CALC_REF = 'pool_calc_ref';
    const VOLUME = 'volume';
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $client_ref;
    /**
     * @var bool
     * @Id
     * @Column(type="boolean")
     */
    public $is_autoship;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $pool_calc_ref;
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $volume;
}