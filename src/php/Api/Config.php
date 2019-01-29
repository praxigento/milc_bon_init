<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api;


interface Config
{
    const BEGINNING_OF_AGES = '2019/01/01 00:00:00';
    const BEGINNING_OF_AGES_FORMAT = 'Y/m/d H:i:s';

    const BONUS_PERIOD_STATE_CLOSE = 'close';
    const BONUS_PERIOD_STATE_OPEN = 'open';

    const BONUS_PERIOD_TYPE_DAY = 1;
    const BONUS_PERIOD_TYPE_MONTH = 3;
    const BONUS_PERIOD_TYPE_WEEK = 2;
    const BONUS_PERIOD_TYPE_YEAR = 4;

    const CALC_TYPE_BONUS_LEVEL_BASED = 'BONUS_LEVEL_BASED';
    const CALC_TYPE_COLLECT_CV = 'COLLECT_CV';
    const CALC_TYPE_QUALIFY_RANK = 'QUALIFY_RANK';
    const CALC_TYPE_TREE_PLAIN = 'TREE_PLAIN';

    const QUAL_RULE_TYPE_GROUP = 'group';
    const QUAL_RULE_TYPE_PV = 'pv';
    const QUAL_RULE_TYPE_RANK = 'rank';

    const RANK_ANGEL = 'ANG';
    const RANK_GOD = 'GOD';
    const RANK_HERO = 'HER';
    const RANK_HUMAN = 'HUM';

    const RULE_GROUP_LOGIC_AND = 'AND';
    const RULE_GROUP_LOGIC_OR = 'OR';

    const SUITE_NOTE = 'Development calcs suite (monthly based).';
}