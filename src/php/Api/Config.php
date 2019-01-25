<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api;


interface Config
{
    const BONUS_PERIOD_TYPE_DAY = 1;
    const BONUS_PERIOD_TYPE_MONTH = 3;
    const BONUS_PERIOD_TYPE_WEEK = 2;
    const BONUS_PERIOD_TYPE_YEAR = 4;
}