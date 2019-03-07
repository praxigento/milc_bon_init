<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper\Init;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan as EPlan;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as ESuite;

interface Bonus
{

    public function calcTypes();

    public function commLevels($calcId, $ranks);

    /**
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan
     */
    public function plan(): EPlan;

    /**
     * Create/load plan ranks and return ranks IDs.
     *
     * @param int $planId
     * @return int[]
     */
    public function planRanks($planId);

    /**
     * Create ranks qualification rules for given qualification calculation.
     *
     * @param int $calcId
     * @param int[] $ranks
     */
    public function qualRules($calcId, $ranks);

    /**
     * Create new suite of Unilevel based cals.
     *
     * @param $planId
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite
     */
    public function suiteUnilevel($planId): ESuite;

    /**
     * Create suite calculations for Unilevel Suite.
     *
     * @param $suiteId
     * @param $typeIds
     * @return array [calc_type_code=>suite_calc_id]
     */
    public function suiteUnilevelCalcs($suiteId, $typeIds);
}