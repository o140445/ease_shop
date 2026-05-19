ALTER TABLE `s_shop_order_recycle`
  ADD COLUMN `recycle_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '回收金额' AFTER `pay_amount`,
  ADD COLUMN `audit_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核管理员ID' AFTER `recycle_admin_id`,
  ADD COLUMN `audittime` int(10) unsigned DEFAULT NULL COMMENT '审核时间' AFTER `recycletime`;

ALTER TABLE `s_shop_order_recycle`
  MODIFY COLUMN `status` enum('pending','approved','rejected','recycled','restored','deleted') NOT NULL DEFAULT 'pending' COMMENT '状态:pending=待审核,approved=已通过,rejected=已拒绝,recycled=已回收,restored=已恢复,deleted=已彻底删除';

ALTER TABLE `s_shop_order_recycle_log`
  MODIFY COLUMN `action` enum('recycle','approve','reject','restore','delete') NOT NULL DEFAULT 'recycle' COMMENT '操作:recycle=提交回收,approve=审核通过,reject=审核拒绝,restore=恢复,delete=彻底删除';

ALTER TABLE `s_shop_balance_log`
  MODIFY COLUMN `type` enum('recharge','pay','refund','recycle','withdraw','withdraw_reject','adjust') NOT NULL DEFAULT 'adjust' COMMENT '类型:recharge=充值,pay=支付,refund=退款,recycle=订单回收,withdraw=提款,withdraw_reject=提款驳回,adjust=调整';

UPDATE `s_shop_order_recycle`
SET `status` = 'approved',
    `recycle_amount` = IF(`recycle_amount` > 0, `recycle_amount`, `pay_amount`),
    `audit_admin_id` = IF(`audit_admin_id` > 0, `audit_admin_id`, `recycle_admin_id`),
    `audittime` = IFNULL(`audittime`, `recycletime`)
WHERE `status` = 'recycled';

INSERT INTO `s_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT 'file', (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle') AS p), `rule_name`, `rule_title`, 'fa fa-circle-o', '', '', '', 0, NULL, '', `rule_py`, `rule_pinyin`, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM (
    SELECT 'shop.order.recycle/approve' AS `rule_name`, '审核通过' AS `rule_title`, 'shtg' AS `rule_py`, 'shenhetongguo' AS `rule_pinyin`
    UNION ALL
    SELECT 'shop.order.recycle/reject', '审核拒绝', 'shjj', 'shenhejujue'
) AS rules
WHERE NOT EXISTS (SELECT 1 FROM `s_auth_rule` WHERE `name` = rules.`rule_name`);

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle/approve'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle/approve'), `rules`)
  AND (
      FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle/index'), `rules`)
  );

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle/reject'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle/reject'), `rules`)
  AND (
      FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle/index'), `rules`)
  );
