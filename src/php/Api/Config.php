<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api;


interface Config
{
    /**/
    const BEGINNING_OF_AGES = '2018/01/01 00:00:00';
    const BEGINNING_OF_AGES_FORMAT = 'Y/m/d H:i:s';
    const BEGINNING_OF_AGES_INC_MAX = 9000; //max random increment on events (in seconds)
    /**/
    const BONUS_PERIOD_STATE_CLOSE = 'close';
    const BONUS_PERIOD_STATE_OPEN = 'open';
    /**/
    const BONUS_PERIOD_TYPE_DAY = 'D';
    const BONUS_PERIOD_TYPE_MONTH = 'M';
    const BONUS_PERIOD_TYPE_WEEK = 'W';
    const BONUS_PERIOD_TYPE_YEAR = 'Y';
    /**/
    const CALC_TYPE_COMM_BINARY = 'COMM_BINARY';
    const CALC_TYPE_COMM_LEVEL_BASED = 'COMM_LEVEL_BASED';
    const CALC_TYPE_COMPRESSION = 'COMPRESSION';
    const CALC_TYPE_CV_COLLECT = 'CV_COLLECT';
    const CALC_TYPE_CV_GROUPING_GV = 'CV_GROUPING_GV';
    const CALC_TYPE_CV_GROUPING_OV = 'CV_GROUPING_OV';
    const CALC_TYPE_CV_GROUPING_PV = 'CV_GROUPING_PV';
    const CALC_TYPE_DOWNGRADE = 'DOWNGRADE';
    const CALC_TYPE_RANK_QUAL = 'RANK_QUAL';
    const CALC_TYPE_TREE_BINARY = 'TREE_BINARY';
    const CALC_TYPE_TREE_MATRIX = 'TREE_MATRIX';
    const CALC_TYPE_TREE_NATURAL = 'TREE_NATURAL';
    /**/
    const CV_REG_SOURCE_SALE = 'sale';
    const CV_REG_SOURCE_SALE_BACK = 'sale_back';
    /**/
    const DB_DRIVER_MYSQL = 'pdo_mysql';
    const DB_DRIVER_POSTGRES = 'pdo_pgsql';
    /**/
    const DB_TBL_BON_CALC_COMM_LEVEL = 'bon_calc_comm_level';
    const DB_TBL_BON_CALC_RANK = 'bon_calc_rank';
    const DB_TBL_BON_CALC_RANK_RULE = 'bon_calc_rank_rule';
    const DB_TBL_BON_CALC_RANK_RULE_GROUP = 'bon_calc_rank_rule_group';
    const DB_TBL_BON_CALC_RANK_RULE_GROUP_REF = 'bon_calc_rank_rule_group_ref';
    const DB_TBL_BON_CALC_RANK_RULE_PV = 'bon_calc_rank_rule_pv';
    const DB_TBL_BON_CALC_RANK_RULE_RANK = 'bon_calc_rank_rule_rank';
    const DB_TBL_BON_CV_REG = 'bon_cv_reg';
    const DB_TBL_BON_CV_REG_SALE = 'bon_cv_reg_sale';
    const DB_TBL_BON_CV_REG_SALE_BACK = 'bon_cv_reg_sale_back';
    const DB_TBL_BON_DWNL_REG = 'bon_dwnl_reg';
    const DB_TBL_BON_DWNL_TREE = 'bon_dwnl_tree';
    const DB_TBL_BON_DWNL_TREE_BIN = 'bon_dwnl_tree_bin';
    const DB_TBL_BON_EVENT_LOG = 'bon_event_log';
    const DB_TBL_BON_EVENT_LOG_DWNL_DEL = 'bon_event_log_dwnl_del';
    const DB_TBL_BON_EVENT_LOG_DWNL_TREE = 'bon_event_log_dwnl_tree';
    const DB_TBL_BON_EVENT_LOG_DWNL_TYPE = 'bon_event_log_dwnl_type';
    const DB_TBL_BON_PLAN = 'bon_plan';
    const DB_TBL_BON_PLAN_CALC_TYPE = 'bon_plan_calc_type';
    const DB_TBL_BON_PLAN_CALC_TYPE_DEPS_AFTER = 'bon_plan_calc_type_deps_after';
    const DB_TBL_BON_PLAN_CALC_TYPE_DEPS_BEFORE = 'bon_plan_calc_type_deps_before';
    const DB_TBL_BON_PLAN_RANK = 'bon_plan_rank';
    const DB_TBL_BON_PLAN_SUITE = 'bon_plan_suite';
    const DB_TBL_BON_PLAN_SUITE_CALC = 'bon_plan_suite_calc';
    const DB_TBL_BON_POOL = 'bon_pool';
    const DB_TBL_BON_POOL_CALC = 'bon_pool_calc';
    const DB_TBL_BON_POOL_COMM_LEVEL = 'bon_pool_comm_level';
    const DB_TBL_BON_POOL_COMM_LEVEL_QUANT = 'bon_pool_comm_level_quant';
    const DB_TBL_BON_POOL_CV = 'bon_pool_cv';
    const DB_TBL_BON_POOL_PERIOD = 'bon_pool_period';
    const DB_TBL_BON_POOL_RANK = 'bon_pool_rank';
    const DB_TBL_BON_POOL_TREE = 'bon_pool_tree';
    const DB_TBL_BON_POOL_TREE_GV = 'bon_pool_tree_gv';
    const DB_TBL_BON_POOL_TREE_OV = 'bon_pool_tree_ov';
    const DB_TBL_BON_POOL_TREE_PV = 'bon_pool_tree_pv';
    const DB_TBL_BON_POOL_TREE_PV_LINK = 'bon_pool_tree_pv_link';
    const DB_TBL_RES_PARTNER = 'res_partner';
    const DB_TBL_SALE_ORDER = 'sale_order';
    /**/
    const EVENT_LOG_TYPE_DWNL_DELETE = 'dwnl.del';
    const EVENT_LOG_TYPE_DWNL_TREE = 'dwnl.tree';
    const EVENT_LOG_TYPE_DWNL_TYPE = 'dwnl.type';
    /**/
    const FORMAT_DATE = 'Y-m-d';
    const FORMAT_DATETIME = 'Y-m-d H:i:s';
    /**/
    const QUAL_RULE_TYPE_GROUP = 'group';
    const QUAL_RULE_TYPE_PV = 'pv';
    const QUAL_RULE_TYPE_RANK = 'rank';
    /**/
    const RANK_ANGEL = 'ANG';
    const RANK_GOD = 'GOD';
    const RANK_HERO = 'HER';
    const RANK_HUMAN = 'HUM';
    /**/
    const RULE_GROUP_LOGIC_AND = 'AND';
    const RULE_GROUP_LOGIC_OR = 'OR';
    /**/
    const TREE_DEPTH_INIT = 1;
    const TREE_PS = ':';
    /**/
    const ZERO = 0.00001;

}