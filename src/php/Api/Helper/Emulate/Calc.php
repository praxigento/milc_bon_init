<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper\Emulate;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as ESuite;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc as ESuiteCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool as EPool;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Calc as EPoolCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Period as EPoolPeriod;

/**
 * Emulate set of calculations.
 */
interface Calc
{
    /**
     * Max date with CV movements.
     *
     * @return \DateTime
     */
    public function getDateMax(): \DateTime;

    /**
     * Get development suite.
     *
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite
     */
    public function getSuite(): ESuite;

    /**
     * @param int $suiteId
     * @param string $code
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc
     */
    public function getSuiteCalc($suiteId, $code): ESuiteCalc;

    /**
     * Register new period for suite calculations.
     *
     * @param string $dateBegin '2019-05-01'
     * @param int $suiteId
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Period
     */
    public function registerPeriod($dateBegin, $suiteId): EPoolPeriod;

    /**
     * Register new pool for given period linked to the suite.
     *
     * @param int $periodId
     * @param string $dateStarted
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool
     */
    public function registerPool($periodId, $dateStarted): EPool;

    /**
     * Register new pool calculation instance for given suite calculation.
     *
     * @param int $poolId
     * @param int $suiteCalcId
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Calc
     */
    public function registerPoolCalc($poolId, $suiteCalcId): EPoolCalc;

    /**
     * CV collection.
     *
     * @param int $poolCalcId
     * @param string $dateFrom
     * @param string $dateTo
     * @return mixed
     */
    public function step01Cv($poolCalcId, $dateFrom, $dateTo);

    /**
     * Downline tree composition.
     *
     * @param int $poolCalcId
     * @param int $poolCalcIdCvCollect
     * @param string $dateTo
     * @return mixed
     */
    public function step02Tree($poolCalcId, $poolCalcIdCvCollect, $dateTo);

    /**
     * Qualification rank calculation.
     *
     * @param int $poolCalcId
     * @param int $poolCalcIdTree
     * @return mixed
     */
    public function step03Rank($poolCalcId, $poolCalcIdTree);

    /**
     * Level based commission calculation.
     *
     * @param int $poolCalcId
     * @param int $poolCalcIdTree
     * @param int $poolCalcIdQual
     * @return mixed
     */
    public function step04Comm($poolCalcId, $poolCalcIdTree, $poolCalcIdQual);
}