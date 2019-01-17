<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus;

/**
 * DB schema related constants (names for tables, columns, ...).
 */
interface DbSchema
{
    const T_BON_BINARY_TREE = 'bon_binary_tree';
    const T_BON_BINARY_TREE_C_CUSTOMER_REF = 'customer_ref';
    const T_BON_BINARY_TREE_C_PARENT_REF = 'parent_ref';
    const T_DWN_TREE = 'dwn_tree';
    const T_DWN_TREE_C_CUSTOMER_REF = 'customer_ref';
    const T_DWN_TREE_C_PARENT_REF = 'parent_ref';
    const T_DWN_TREE_TRACE = 'dwn_tree_trace';
    const T_DWN_TREE_TRACE_C_CUSTOMER_REF = 'customer_ref';
    const T_DWN_TREE_TRACE_C_DATE = 'date';
    const T_DWN_TREE_TRACE_C_ID = 'id';
    const T_DWN_TREE_TRACE_C_PARENT_REF = 'parent_ref';
    const T_RES_PARTNER = 'res_partner';
    const T_RES_PARTNER_F_ID = 'id';
}