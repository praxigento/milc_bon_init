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

    public function getTimestampFrom($datestamp, $periodType = self::TYPE_DAY);

}