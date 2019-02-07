<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api;


interface Config
{
    /**/
    const BEGINNING_OF_AGES = '2019/01/01 00:00:00';
    const BEGINNING_OF_AGES_FORMAT = 'Y/m/d H:i:s';
    /**/
    const BONUS_PERIOD_STATE_CLOSE = 'close';
    const BONUS_PERIOD_STATE_OPEN = 'open';
    /**/
    const BONUS_PERIOD_TYPE_DAY = 1;
    const BONUS_PERIOD_TYPE_MONTH = 3;
    const BONUS_PERIOD_TYPE_WEEK = 2;
    const BONUS_PERIOD_TYPE_YEAR = 4;
    /**/
    const CALC_TYPE_BONUS_LEVEL_BASED = 'BONUS_LEVEL_BASED';
    const CALC_TYPE_COLLECT_CV = 'COLLECT_CV';
    const CALC_TYPE_QUALIFY_RANK_SIMPLE = 'QUALIFY_RANK_SIMPLE';
    const CALC_TYPE_TREE_PLAIN = 'TREE_PLAIN';
    /**/
    const CV_REG_SOURCE_SALE = 'sale';
    const CV_REG_SOURCE_SALE_BACK = 'sale_back';
    /**/
    const DB_DRIVER_MYSQL = 'pdo_mysql';
    const DB_DRIVER_POSTGRES = 'pdo_pgsql';
    /**/
    const DB_TBL_BON_CALC_TYPE = 'bon_calc_type';
    const DB_TBL_BON_CALC_TYPE_DEPS_BEFORE = 'bon_calc_type_deps_before';
    const DB_TBL_BON_CALC_TYPE_DEPS_ON = 'bon_calc_type_deps_on';
    const DB_TBL_BON_CV_COLLECT = 'bon_cv_collect';
    const DB_TBL_BON_CV_REG = 'bon_cv_reg';
    const DB_TBL_BON_CV_REG_SALE = 'bon_cv_reg_sale';
    const DB_TBL_BON_CV_REG_SALE_BACK = 'bon_cv_reg_sale_back';
    const DB_TBL_BON_PERIOD = 'bon_period';
    const DB_TBL_BON_PERIOD_CALC = 'bon_period_calc';
    const DB_TBL_BON_PERIOD_RANK = 'bon_period_rank';
    const DB_TBL_BON_PERIOD_TREE = 'bon_period_tree';
    const DB_TBL_BON_PLAN = 'bon_plan';
    const DB_TBL_BON_PLAN_LEVEL = 'bon_plan_level';
    const DB_TBL_BON_PLAN_QUAL = 'bon_plan_qual';
    const DB_TBL_BON_PLAN_RANK = 'bon_plan_rank';
    const DB_TBL_BON_QUAL_RULE = 'bon_qual_rule';
    const DB_TBL_BON_QUAL_RULE_GROUP = 'bon_qual_rule_group';
    const DB_TBL_BON_QUAL_RULE_GROUP_REF = 'bon_qual_rule_group_ref';
    const DB_TBL_BON_QUAL_RULE_PV = 'bon_qual_rule_pv';
    const DB_TBL_BON_QUAL_RULE_RANK = 'bon_qual_rule_rank';
    const DB_TBL_BON_SUITE = 'bon_suite';
    const DB_TBL_BON_SUITE_CALC = 'bon_suite_calc';
    const DB_TBL_CLIENT_REG = 'client_reg';
    const DB_TBL_CLIENT_REG_LOG_DEL = 'client_reg_log_del';
    const DB_TBL_CLIENT_REG_LOG_TYPE = 'client_reg_log_type';
    const DB_TBL_CLIENT_TREE = 'client_tree';
    const DB_TBL_CLIENT_TREE_LOG = 'client_tree_log';
    const DB_TBL_RES_PARTNER = 'res_partner';
    const DB_TBL_SALE_ORDER = 'sale_order';
    /**/
    const QUAL_RULE_TYPE_GROUP = 'group';
    /**/
    const QUAL_RULE_TYPE_PV = 'pv';
    const QUAL_RULE_TYPE_RANK = 'rank';
    const RANK_ANGEL = 'ANG';
    const RANK_GOD = 'GOD';
    /**/
    const RANK_HERO = 'HER';
    const RANK_HUMAN = 'HUM';
    /**/
    const RULE_GROUP_LOGIC_AND = 'AND';
    const RULE_GROUP_LOGIC_OR = 'OR';
    const SUITE_NOTE = 'Development calcs suite (monthly based).';
}