-- Order workflow adjustments: hide refund menu, expose recycle menu, and add quick status actions.

UPDATE `s_auth_rule`
SET `ismenu` = 0,
    `status` = 'hidden',
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` = 'shop/refund' OR `name` LIKE 'shop/refund/%';

INSERT INTO `s_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT 'file', (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'order') AS p), 'shop.order.recycle', '回收订单', 'fa fa-recycle', '', '', '', 1, NULL, '', 'hsdd', 'huishoudingdan', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle');

UPDATE `s_auth_rule`
SET `pid` = (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'order') AS p),
    `title` = '回收订单',
    `icon` = 'fa fa-recycle',
    `ismenu` = 1,
    `status` = 'normal',
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` = 'shop.order.recycle';

INSERT INTO `s_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT 'file', (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order') AS p), `rule_name`, `rule_title`, 'fa fa-circle-o', '', '', '', 0, NULL, '', `rule_py`, `rule_pinyin`, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM (
    SELECT 'shop/order/ship' AS `rule_name`, '发货' AS `rule_title`, 'fh' AS `rule_py`, 'fahuo' AS `rule_pinyin`
    UNION ALL
    SELECT 'shop/order/complete', '完成', 'wc', 'wancheng'
    UNION ALL
    SELECT 'shop/order/recycle', '回收订单', 'hsdd', 'huishoudingdan'
) AS rules
WHERE NOT EXISTS (SELECT 1 FROM `s_auth_rule` WHERE `name` = rules.`rule_name`);

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.order.recycle'), `rules`)
  AND (
      FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'order'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/index'), `rules`)
  );

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/ship'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/ship'), `rules`)
  AND (
      FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/index'), `rules`)
  );

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/complete'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/complete'), `rules`)
  AND (
      FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/index'), `rules`)
  );
