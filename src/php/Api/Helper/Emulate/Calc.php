<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper\Emulate;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as EPlanSuite;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc as EPlanSuiteCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Period as EResPeriod;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race as EResRace;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race\Calc as EResRaceCalc;

/**
 * Emulate set of calculations.
 */
interface Calc
{
    /**
     * Get development suite.
     *
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite
     */
    public function getSuite(): EPlanSuite;

    /**
     * @param int $suiteId
     * @param string $code
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc
     */
    public function getSuiteCalc($suiteId, $code): EPlanSuiteCalc;

    /**
     * Register new period for suite calculations.
     * @param string $dateBegin '2019-05-01'
     * @param int $suiteId
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Period
     */
    public function registerPeriod($dateBegin, $suiteId): EResPeriod;

    /**
     * @param int $periodId
     * @param string $dateStarted
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race
     */
    public function registerRace($periodId, $dateStarted): EResRace;

    /**
     * @param int $raceId
     * @param int $calcId
     * @return \Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race\Calc
     */
    public function registerRaceCalc($raceId, $calcId): EResRaceCalc;

    /**
     * Collect CV on step 01.
     *
     * @param int $raceCalcId
     * @param string $dateFrom
     * @param string $dateTo
     * @return mixed
     */
    public function step01Cv($raceCalcId, $dateFrom, $dateTo);

    /**
     * Compose downline tree on step 02.
     *
     * @param int $raceCalcId
     * @param int $raceCalcIdCvCollect
     * @param string $dateTo
     * @return mixed
     */
    public function step02Tree($raceCalcId, $raceCalcIdCvCollect, $dateTo);

    /**
     * @param int $raceCalcId
     * @param int $raceCalcIdTree
     * @return mixed
     */
    public function step03Qual($raceCalcId, $raceCalcIdTree);

    public function step04Comm($raceCalcId, $raceCalcIdTree, $raceCalcIdQual);
}