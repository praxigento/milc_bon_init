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
    const QUAL_RULE_TYPE_GROUP = 'group';
    const QUAL_RULE_TYPE_PV = 'pv';
    const QUAL_RULE_TYPE_RANK = 'rank';
    const RANK_ANGEL = 'ANG';
    const RANK_GOD = 'GOD';
    const RANK_HERO = 'HER';
    const RANK_HUMAN = 'HUM';
    const RULE_GROUP_LOGIC_AND = 'AND';
    const RULE_GROUP_LOGIC_OR = 'OR';
}