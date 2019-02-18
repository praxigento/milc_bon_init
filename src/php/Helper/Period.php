<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper;

use Praxigento\Milc\Bonus\Api\Config as Cfg;

class Period
    implements \Praxigento\Milc\Bonus\Api\Helper\Period
{
    /** @var array Common cache for periods bounds: [period][type][from|to] = ... */
    private $cachePeriodBounds = [];
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format */
    private $hlpFormat;
    /**
     * Week first and last day by default.
     */
    private $weekFirstDay = self::WEEK_SATURDAY;
    private $weekLastDay = self::WEEK_FRIDAY;

    public function __construct(
        \Praxigento\Milc\Bonus\Api\Helper\Format $hlpFormat
    ) {
        $this->hlpFormat = $hlpFormat;
    }

    /**
     * Calculate period's from/to bounds (month 201508 = "2015-08-01 02:00:00 / 2015-09-01 02:00:00") and cache it.
     * Use "<=" for dateFrom and "<" for dateTo in comparison.
     *
     * @param string $periodValue [20150601 | 201506 | 2015]
     * @param string $periodType [DAY | WEEK | MONTH | YEAR]
     */
    private function calcPeriodBounds($periodValue, $periodType = self::TYPE_DAY)
    {
        $from = null;
        $to = null;
        switch ($periodType) {
            case self::TYPE_DAY:
                $dt = date_create_from_format('Ymd', $periodValue);
                $ts = strtotime('midnight', $dt->getTimestamp());
                $from = date(Cfg::FORMAT_DATETIME, $ts);
                $ts = strtotime('tomorrow midnight', $dt->getTimestamp());
                $to = date(Cfg::FORMAT_DATETIME, $ts);
                break;
            case self::TYPE_WEEK:
                /* week period ends on ...  */
                $end = $this->getWeekLastDay();
                $prev = $this->getWeekDayNext($end);
                /* this should be the last day of the week */
                $periodValue = $this->normalizePeriod($periodValue, self::TYPE_DAY);
                $dt = date_create_from_format('Ymd', $periodValue);
                $ts = strtotime("previous $prev midnight", $dt->getTimestamp());
                $from = date(Cfg::FORMAT_DATETIME, $ts);
                $ts = strtotime('tomorrow midnight', $dt->getTimestamp());
                $to = date(Cfg::FORMAT_DATETIME, $ts);
                break;
            case self::TYPE_MONTH:
                $periodValue = $this->normalizePeriod($periodValue, self::TYPE_MONTH);
                $dt = date_create_from_format('Ymd H:i:s', $periodValue . '01 12:00:00');
                $ts = strtotime('first day of midnight', $dt->getTimestamp());
                $from = date(Cfg::FORMAT_DATETIME, $ts);
                $ts = strtotime('first day of next month midnight', $dt->getTimestamp());
                $to = date(Cfg::FORMAT_DATETIME, $ts);
                break;
            case self::TYPE_YEAR:
                $periodValue = $this->normalizePeriod($periodValue, self::TYPE_YEAR);
                $dt = date_create_from_format('Ymd H:i:s', $periodValue . '0101 12:00:00');
                $ts = strtotime('first day of January', $dt->getTimestamp());
                $from = date(Cfg::FORMAT_DATETIME, $ts);
                $ts = strtotime('first day of January next year midnight', $dt->getTimestamp());
                $to = date(Cfg::FORMAT_DATETIME, $ts);
                break;
        }
        $this->cachePeriodBounds[$periodValue][$periodType]['from'] = $from;
        $this->cachePeriodBounds[$periodValue][$periodType]['to'] = $to;
    }

    public function getPeriodFirstDate($datestamp)
    {
        // TODO: Implement getPeriodFirstDate() method.
    }

    public function getTimestampFrom($date, $periodType = self::TYPE_DAY)
    {
        $periodValue = $this->normalizePeriod($date, $periodType);
        if (
        !isset($this->cachePeriodBounds[$periodValue][$periodType])
        ) {
            $this->calcPeriodBounds($periodValue, $periodType);
        }
        $result = $this->cachePeriodBounds[$periodValue][$periodType]['from'];
        return $result;
    }

    /**
     * @param $weekDay - string see self::WEEK_
     *
     * @return string see self::WEEK_
     */
    private function getWeekDayNext($weekDay)
    {
        switch (strtolower($weekDay)) {
            case self::WEEK_SUNDAY:
                $result = self::WEEK_MONDAY;
                break;
            case self::WEEK_MONDAY:
                $result = self::WEEK_TUESDAY;
                break;
            case self::WEEK_TUESDAY:
                $result = self::WEEK_WEDNESDAY;
                break;
            case self::WEEK_WEDNESDAY:
                $result = self::WEEK_THURSDAY;
                break;
            case self::WEEK_THURSDAY:
                $result = self::WEEK_FRIDAY;
                break;
            case self::WEEK_FRIDAY:
                $result = self::WEEK_SATURDAY;
                break;
            case self::WEEK_SATURDAY:
                $result = self::WEEK_SUNDAY;
                break;
        }
        return $result;
    }

    /**
     * @param $weekDay - string see self::WEEK_
     *
     * @return string see self::WEEK_
     */
    private function getWeekDayPrev($weekDay)
    {
        switch (strtolower($weekDay)) {
            case self::WEEK_SUNDAY:
                $result = self::WEEK_SATURDAY;
                break;
            case self::WEEK_MONDAY:
                $result = self::WEEK_SUNDAY;
                break;
            case self::WEEK_TUESDAY:
                $result = self::WEEK_MONDAY;
                break;
            case self::WEEK_WEDNESDAY:
                $result = self::WEEK_TUESDAY;
                break;
            case self::WEEK_THURSDAY:
                $result = self::WEEK_WEDNESDAY;
                break;
            case self::WEEK_FRIDAY:
                $result = self::WEEK_THURSDAY;
                break;
            case self::WEEK_SATURDAY:
                $result = self::WEEK_FRIDAY;
                break;
        }
        return $result;
    }

    private function normalizePeriod($value, $type = self::TYPE_DAY)
    {
        if ($value instanceof \DateTime) {
            $result = $value->format('Ymd');
        } else {
            $result = substr($value, 0, 8);
        }
        switch ($type) {
            case self::TYPE_MONTH:
                $result = substr($result, 0, 6);
                break;
            case self::TYPE_YEAR:
                $result = substr($result, 0, 4);
                break;
        }
        return $result;
    }


}