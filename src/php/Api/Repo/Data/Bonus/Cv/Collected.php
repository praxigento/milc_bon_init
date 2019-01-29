<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Cv;


/**
 * CV/ACV are collected for period.
 *
 * @Entity
 * @Table(name="bon_cv_collect")
 */
class Collected
    extends \TeqFw\Lib\Data
{
    const CALC_REF = 'calc_ref';
    const CLIENT_REF = 'client_ref';
    const IS_AUTOSHIP = 'is_autoship';
    const VOLUME = 'volume';
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $calc_ref;
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
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $volume;
}