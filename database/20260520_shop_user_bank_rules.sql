INSERT INTO `s_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT 'file', (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'user') AS p), 'shop.user.bank', '会员银行卡', 'fa fa-credit-card', '', '', '', 1, NULL, '', 'hyyhk', 'huiyuanyinhangka', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
WHERE NOT EXISTS (SELECT 1 FROM `s_auth_rule` WHERE `name` = 'shop.user.bank');

UPDATE `s_auth_rule`
SET `pid` = (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'user') AS p),
    `title` = '会员银行卡',
    `icon` = 'fa fa-credit-card',
    `ismenu` = 1,
    `status` = 'normal',
    `updatetime` = UNIX_TIMESTAMP()
WHERE `name` = 'shop.user.bank';

INSERT INTO `s_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT 'file', (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank') AS p), `rule_name`, `rule_title`, 'fa fa-circle-o', '', '', '', 0, NULL, '', `rule_py`, `rule_pinyin`, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM (
    SELECT 'shop.user.bank/index' AS rule_name, '查看' AS rule_title, 'ck' AS rule_py, 'chakan' AS rule_pinyin
    UNION ALL SELECT 'shop.user.bank/add', '添加', 'tj', 'tianjia'
    UNION ALL SELECT 'shop.user.bank/edit', '编辑', 'bj', 'bianji'
    UNION ALL SELECT 'shop.user.bank/del', '删除', 'sc', 'shanchu'
    UNION ALL SELECT 'shop.user.bank/multi', '批量更新', 'plgx', 'pilianggengxin'
    UNION ALL SELECT 'shop.user.bank/recyclebin', '回收站', 'hsz', 'huishouzhan'
    UNION ALL SELECT 'shop.user.bank/restore', '还原', 'hy', 'huanyuan'
    UNION ALL SELECT 'shop.user.bank/destroy', '销毁', 'xh', 'xiaohui'
) AS rules
WHERE NOT EXISTS (SELECT 1 FROM `s_auth_rule` WHERE `name` = rules.`rule_name`);

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank'))
WHERE `status` = 'normal'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank'), `rules`)
  AND (
      FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'user'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/user'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/user/index'), `rules`)
  );

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/index'))
WHERE `status` = 'normal'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/index'), `rules`)
  AND (
      FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/user'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/user/index'), `rules`)
  );

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/add'))
WHERE `status` = 'normal'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/add'), `rules`)
  AND FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/index'), `rules`);

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/edit'))
WHERE `status` = 'normal'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/edit'), `rules`)
  AND FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/index'), `rules`);

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/del'))
WHERE `status` = 'normal'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/del'), `rules`)
  AND FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop.user.bank/index'), `rules`);
