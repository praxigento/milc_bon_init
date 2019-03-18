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
    private const FROM = 'from';
    private const TO = 'to';
    /** @var array Common cache for periods bounds: [period][type][from|to] = ... */
    private $cachePeriodBounds = [];
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format */
    private $hlpFormat;
    /**
     * Week first and last day by default.
     */
    private $weekFirstDay = self::WEEK_MONDAY;
    private $weekLastDay = self::WEEK_SUNDAY;

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
                $periodValue = $this->normalizePeriod($periodValue, self::TYPE_DAY);
                $dtNow = date_create_from_format('Ymd', $periodValue);
                $tsNow = $dtNow->getTimestamp();
                /* first day of the current week  */
                $weekFirst = $this->getWeekFirstDay();
                $tsFirst = strtotime("this $weekFirst midnight", $dtNow->getTimestamp());
                if ($tsFirst > $tsNow) {
                    $tsFirst = strtotime("previous $weekFirst midnight", $dtNow->getTimestamp());
                }
                $from = date(Cfg::FORMAT_DATETIME, $tsFirst);
                /* last day of the current week  */
                $weekLast = $this->getWeekLastDay();
                $tsLast = strtotime("this $weekLast midnight", $dtNow->getTimestamp());
                if ($tsLast < $tsNow) {
                    $tsLast = strtotime("next $weekFirst midnight", $dtNow->getTimestamp());
                }
                $to = date(Cfg::FORMAT_DATETIME, $tsLast);
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
        $this->cachePeriodBounds[$periodValue][$periodType][self::FROM] = $from;
        $this->cachePeriodBounds[$periodValue][$periodType][self::TO] = $to;
    }

    public function getPeriodFirstDate($datestamp)
    {
        // TODO: Implement getPeriodFirstDate() method.
    }

    public function getTimestampFrom($datestamp, $periodType = self::TYPE_DAY)
    {
        $periodValue = $this->normalizePeriod($datestamp, $periodType);
        if (!isset($this->cachePeriodBounds[$periodValue][$periodType])) {
            $this->calcPeriodBounds($periodValue, $periodType);
        }
        $result = $this->cachePeriodBounds[$periodValue][$periodType][self::FROM];
        return $result;
    }

    public function getTimestampTo($datestamp, $periodType = self::TYPE_DAY)
    {
        $periodValue = $this->normalizePeriod($datestamp, $periodType);
        if (!isset($this->cachePeriodBounds[$periodValue][$periodType])) {
            $this->calcPeriodBounds($periodValue, $periodType);
        }
        $result = $this->cachePeriodBounds[$periodValue][$periodType][self::TO];
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

    /**
     * @return string
     */
    public function getWeekFirstDay()
    {
        return $this->weekFirstDay;
    }

    /**
     * @return string
     */
    public function getWeekLastDay()
    {
        return $this->weekLastDay;
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