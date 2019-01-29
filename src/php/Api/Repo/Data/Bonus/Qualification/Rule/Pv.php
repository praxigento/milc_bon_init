<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Qualification\Rule;


/**
 * PV based rules.
 *
 * @Entity
 * @Table(name="bon_qual_rule_pv")
 */
class Pv
    extends \TeqFw\Lib\Data
{
    const AUTOSHIP_ONLY = 'autoship_only';
    const PERIOD = 'period';
    const REF = 'ref';
    const VOLUME = 'volume';

    /**
     * @var bool
     * @Column(type="boolean")
     */
    public $autoship_only;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $period;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $ref;
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $volume;
}