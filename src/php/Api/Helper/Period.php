<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper;

/**
 * Utilities to manipulate with period related data.
 */
interface Period
{
    const TYPE_DAY = 'DAY';
    const TYPE_MONTH = 'MONTH';
    const TYPE_WEEK = 'WEEK';
    const TYPE_YEAR = 'YEAR';

    const WEEK_FRIDAY = 'friday';
    const WEEK_MONDAY = 'monday';
    const WEEK_SATURDAY = 'saturday';
    const WEEK_SUNDAY = 'sunday';
    const WEEK_THURSDAY = 'thursday';
    const WEEK_TUESDAY = 'tuesday';
    const WEEK_WEDNESDAY = 'wednesday';

    public function getPeriodFirstDate($datestamp);

    /**
     * @return string
     */
    public function getWeekFirstDay();

    /**
     * @return string
     */
    public function getWeekLastDay();

    /**
     * Get date-time value for "date >= :dateFrom" statements.
     *
     * @param string $datestamp
     * @param string $periodType
     * @return string
     */
    public function getTimestampFrom($datestamp, $periodType = self::TYPE_DAY);

    /**
     * Get date-time value for "date < :dateTo" statements.
     *
     * @param string $datestamp
     * @param string $periodType
     * @return string
     */
    public function getTimestampTo($datestamp, $periodType = self::TYPE_DAY);

}