CREATE
  OR REPLACE
  VIEW bon_ui_plan_calcs AS
select sc.suite_ref AS suite_id,
       sc.id        AS suite_calc_id,
       t.code       AS suite_calc_type,
       sc.sequence  AS suite_calc_order,
       s.period     AS suite_period
from ((bon_plan_suite s
  left join bon_plan_suite_calc sc on
    ((sc.suite_ref = s.id)))
       left join bon_plan_calc_type t on
  ((t.id = sc.type_ref)));

CREATE
  OR REPLACE
  VIEW bon_ui_plan_ranks AS
select pr.plan_ref AS plan_id,
       pr.id       AS rank_id,
       pr.code     AS rank_code,
       pr.note     AS rank_desc,
       pr.sequence AS rank_order,
       cr.calc_ref AS suite_calc_id,
       ct.code     AS suite_calc_type,
       cr.rule_ref AS root_rule_id
from (((bon_plan_rank pr
  left join bon_calc_rank cr on
    ((cr.rank_ref = pr.id)))
  left join bon_plan_suite_calc sc on
    ((sc.id = cr.calc_ref)))
       left join bon_plan_calc_type ct on
  ((ct.id = sc.type_ref)));

CREATE
  OR REPLACE
  VIEW bon_ui_rank_rules AS
select crr.id           AS rule_id,
       crr.type         AS rule_type,
       rg.logic         AS group_logic,
       rgr.grouped_ref  AS grouped_rule_id,
       pv.period        AS pv_period,
       pv.autoship_only AS pv_autoship,
       pv.volume        AS pv_volume,
       pr.code          AS rank_code,
       rnk.period       AS rank_period,
       rnk.count        AS rank_count
from (((((bon_calc_rank_rule crr
  left join bon_calc_rank_rule_group rg on
    ((rg.ref = crr.id)))
  left join bon_calc_rank_rule_group_ref rgr on
    ((rgr.grouping_ref = rg.ref)))
  left join bon_calc_rank_rule_pv pv on
    ((pv.ref = crr.id)))
  left join bon_calc_rank_rule_rank rnk on
    ((rnk.ref = crr.id)))
       left join bon_plan_rank pr on
  ((pr.id = rnk.rank_ref)));

CREATE
  OR REPLACE
  VIEW bon_ui_comm_level AS
select pr.sequence AS rank_order,
       pr.code     AS rank_code,
       cl.level    AS tree_level,
       cl.percent  AS comm_percent,
       ct.code     AS suite_calc_type
from (((bon_calc_comm_level cl
  left join bon_plan_rank pr on
    ((pr.id = cl.rank_ref)))
  left join bon_plan_suite_calc sc on
    ((sc.id = cl.calc_ref)))
       left join bon_plan_calc_type ct on
  ((ct.id = sc.type_ref)));

CREATE
  OR REPLACE
  VIEW bon_ui_reg_cv AS
select reg.id          AS id,
       reg.client_ref  AS client_id,
       reg.volume      AS volume,
       reg.is_autoship AS is_autoship,
       reg.date        AS date,
       reg.type        AS type,
       sale.source_ref AS sale_id,
       back.source_ref AS back_id
from ((bon_cv_reg reg
  left join bon_cv_reg_sale sale on
    ((sale.registry_ref = reg.id)))
       left join bon_cv_reg_sale_back back on
  ((back.registry_ref = reg.id)));

CREATE
  OR REPLACE
  VIEW bon_ui_reg_tree AS
select tr.client_ref   AS client_id,
       reg.mlm_id      AS client_mlm_id,
       tr.parent_ref   AS parent_id,
       par.mlm_id      AS parent_mlm_id,
       tr.depth        AS depth,
       tr.path         AS path,
       reg.is_customer AS is_customer
from ((dwnl_tree tr
  left join dwnl_reg reg on
    ((reg.client_ref = tr.client_ref)))
       left join dwnl_reg par on
  ((par.client_ref = tr.parent_ref)));

CREATE
  OR REPLACE
  VIEW bon_ui_calc_pool AS
select pc.id          AS pool_calc_id,
       ct.code        AS calc_type,
       pp.suite_ref   AS suite_id,
       p.id           AS pool_id,
       p.date_started AS pool_started,
       pp.id          AS period_id,
       pp.state       AS period_state,
       ps.period      AS period_type,
       pp.date_begin  AS period_begin
from ((((bon_pool p
  left join bon_pool_period pp on
    ((pp.id = p.period_ref)))
  left join bon_pool_calc pc on
    ((pc.pool_ref = p.id)))
  left join bon_plan_suite ps on
    ((ps.id = pp.suite_ref)))
       left join bon_plan_calc_type ct on
  ((ct.id = pc.calc_ref)));

CREATE
  OR REPLACE
  VIEW bon_ui_calc_cv AS
select pc.id           AS pool_calc_id,
       pc.calc_ref     AS suite_calc_id,
       ci.cv_reg_ref   AS cv_reg_item_id,
       cvr.client_ref  AS client_id,
       cvr.volume      AS cv_volume,
       cvr.is_autoship AS is_autoship,
       cvr.date        AS cv_reg_date,
       cvr.type        AS cv_reg_type
from ((bon_pool_cv ci
  left join bon_pool_calc pc on
    ((pc.id = ci.pool_calc_ref)))
       left join bon_cv_reg cvr on
  ((cvr.id = ci.cv_reg_ref)));

CREATE
  OR REPLACE
  VIEW `bon_ui_calc_tree` AS
select `tr`.`pool_calc_ref`      AS `pool_calc_id`,
       `tr`.`client_ref`         AS `client_id`,
       `tr`.`parent_ref`         AS `parent_id`,
       count(`trq`.`cv_reg_ref`) AS `total_orders`,
       `tpv`.`pv`                AS `pv`,
       `tpv`.`apv`               AS `apv`
from `bon_pool_tree` `tr`
       left join `bon_pool_tree_quant` `trq` on
  `trq`.`tree_node_ref` = `tr`.`id`
       left join `bon_pool_tree_pv` `tpv` on
  `tpv`.`tree_node_ref` = `tr`.`id`
group by `tr`.`pool_calc_ref`,
         `tr`.`client_ref`,
         `tr`.`parent_ref`,
         `tpv`.`pv`,
         `tpv`.`apv`;

CREATE
  OR REPLACE
  VIEW bon_ui_calc_comm AS
select cl.id                 AS comm_id,
       cl.pool_calc_ref      AS pool_calc_id,
       cl.client_ref         AS client_id,
       cl.level              AS level,
       cl.cv                 AS level_cv,
       cl.percent            AS level_percent,
       cl.commission         AS level_comm,
       sum(clq.value)        AS quants_sum,
       count(clq.cv_reg_ref) AS quants_count
from (bon_pool_comm_level cl
       left join bon_pool_comm_level_quant clq on
  ((clq.comm_ref = cl.id)))
group by cl.id;

CREATE
  OR REPLACE
  VIEW bon_ui_calc_comm_quant AS
SELECT cl.id            as comm_id,
       cl.pool_calc_ref as pool_calc_id,
       cl.client_ref    as comm_client_id,
       cl.cv            as comm_cv,
       cl.level         as comm_level,
       cl.percent       as comm_percent,
       cl.commission    as comm_total,
       clq.value        as quant_value,
       clq.cv_reg_ref   as quant_reg_id,
       cr.client_ref    as quant_client_ref,
       cr.volume        as quant_cv,
       cr.is_autoship   as is_autoship,
       cr.type          as quant_type,
       cr.date          as quant_date,
       crs.source_ref   as quant_sale_id
FROM bon_pool_comm_level_quant as clq
       LEFT JOIN bon_pool_comm_level as cl ON
  cl.id = clq.comm_ref
       LEFT JOIN bon_cv_reg as cr ON
  cr.id = clq.cv_reg_ref
       LEFT JOIN bon_cv_reg_sale as crs ON
  crs.registry_ref = cr.id