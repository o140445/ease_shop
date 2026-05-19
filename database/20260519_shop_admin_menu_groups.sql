-- Re-group shop backend menus into top-level business sections.
-- Routes stay unchanged; only auth_rule menu hierarchy/title is adjusted.

UPDATE `s_auth_rule`
SET `title` = '商品管理',
    `icon` = 'fa fa-shopping-bag',
    `pid` = 0,
    `ismenu` = 1,
    `status` = 'normal',
    `weigh` = 95,
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` = 'product';

UPDATE `s_auth_rule`
SET `title` = '会员管理',
    `icon` = 'fa fa-users',
    `pid` = 0,
    `ismenu` = 1,
    `status` = 'normal',
    `weigh` = 90,
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` = 'user';

INSERT INTO `s_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT 'file', 0, 'order', '订单管理', 'fa fa-shopping-cart', '', '', '', 1, NULL, '', 'ddgl', 'dingdanguanli', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 85, 'normal'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `s_auth_rule` WHERE `name` = 'order');

INSERT INTO `s_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT 'file', 0, 'finance', '财务管理', 'fa fa-credit-card', '', '', '', 1, NULL, '', 'cwgl', 'caiwuguanli', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 80, 'normal'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `s_auth_rule` WHERE `name` = 'finance');

INSERT INTO `s_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT 'file', 0, 'shopconfig', '配置', 'fa fa-cogs', '', '', '', 1, NULL, '', 'pz', 'peizhi', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 75, 'normal'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `s_auth_rule` WHERE `name` = 'shopconfig');

UPDATE `s_auth_rule`
SET `pid` = (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'product') AS t),
    `title` = CASE `name`
        WHEN 'shop/category' THEN '分类'
        WHEN 'shop/product' THEN '商品'
        WHEN 'shop/favorite' THEN '商品收藏'
        ELSE `title`
    END,
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` IN ('shop/category', 'shop/product', 'shop/favorite');

UPDATE `s_auth_rule`
SET `pid` = (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'user') AS t),
    `title` = CASE `name`
        WHEN 'shop/user' THEN '会员'
        WHEN 'shop/level' THEN '会员等级'
        ELSE `title`
    END,
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` IN ('shop/user', 'shop/level');

UPDATE `s_auth_rule`
SET `pid` = (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'order') AS t),
    `title` = CASE `name`
        WHEN 'shop/cart' THEN '购物车'
        WHEN 'shop/order' THEN '订单'
        WHEN 'shop.order.recycle' THEN '回收订单'
        ELSE `title`
    END,
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` IN ('shop/cart', 'shop/order', 'shop.order.recycle');

UPDATE `s_auth_rule`
SET `ismenu` = 0,
    `status` = 'hidden',
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` = 'shop/refund' OR `name` LIKE 'shop/refund/%';

UPDATE `s_auth_rule`
SET `pid` = (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'finance') AS t),
    `title` = CASE `name`
        WHEN 'shop/balance' THEN '余额管理'
        WHEN 'shop/balance/log' THEN '余额流水'
        WHEN 'shop/recharge' THEN '余额充值'
        WHEN 'shop/withdraw' THEN '提款申请'
        ELSE `title`
    END,
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` IN ('shop/balance', 'shop/balance/log', 'shop/recharge', 'shop/withdraw');

UPDATE `s_auth_rule`
SET `ismenu` = 0,
    `status` = 'hidden',
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` = 'shop/balance';

UPDATE `s_auth_rule`
SET `pid` = (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shopconfig') AS t),
    `title` = CASE `name`
        WHEN 'shop/notice' THEN '公告'
        WHEN 'shop/config' THEN '商城配置'
        WHEN 'shop/banner' THEN '首页Banner'
        WHEN 'shop/nav' THEN '导航'
        WHEN 'shop/home' THEN '首页管理'
        ELSE `title`
    END,
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` IN ('shop/notice', 'shop/config', 'shop/banner', 'shop/nav', 'shop/home');

UPDATE `s_auth_rule`
SET `title` = '首页模块',
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` = 'shop/home/module';

UPDATE `s_auth_rule`
SET `title` = '商城',
    `ismenu` = 0,
    `status` = 'hidden',
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` = 'shop';

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'product'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'product'), `rules`)
  AND EXISTS (
      SELECT 1 FROM `s_auth_rule` AS r
      WHERE r.`name` IN ('shop/category', 'shop/product', 'shop/favorite')
        AND FIND_IN_SET(r.`id`, `s_auth_group`.`rules`)
  );

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'user'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'user'), `rules`)
  AND EXISTS (
      SELECT 1 FROM `s_auth_rule` AS r
      WHERE r.`name` IN ('shop/user', 'shop/level')
        AND FIND_IN_SET(r.`id`, `s_auth_group`.`rules`)
  );

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'order'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'order'), `rules`)
  AND EXISTS (
      SELECT 1 FROM `s_auth_rule` AS r
      WHERE r.`name` IN ('shop/cart', 'shop/order', 'shop.order.recycle')
        AND FIND_IN_SET(r.`id`, `s_auth_group`.`rules`)
  );

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'finance'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'finance'), `rules`)
  AND EXISTS (
      SELECT 1 FROM `s_auth_rule` AS r
      WHERE r.`name` IN ('shop/balance/log', 'shop/recharge', 'shop/withdraw')
        AND FIND_IN_SET(r.`id`, `s_auth_group`.`rules`)
  );

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shopconfig'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shopconfig'), `rules`)
  AND EXISTS (
      SELECT 1 FROM `s_auth_rule` AS r
      WHERE r.`name` IN ('shop/notice', 'shop/config', 'shop/banner', 'shop/nav', 'shop/home')
        AND FIND_IN_SET(r.`id`, `s_auth_group`.`rules`)
  );
