INSERT INTO `s_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `url`, `condition`, `remark`, `ismenu`, `menutype`, `extend`, `py`, `pinyin`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT 'file', (SELECT `id` FROM (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order') AS p), 'shop/order/detail', '详情', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'xq', 'xiangqing', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `s_auth_rule` WHERE `name` = 'shop/order/detail');

UPDATE `s_auth_group`
SET `rules` = CONCAT_WS(',', NULLIF(`rules`, ''), (SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/detail'))
WHERE `rules` <> '*'
  AND NOT FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/detail'), `rules`)
  AND (
      FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order'), `rules`)
      OR FIND_IN_SET((SELECT `id` FROM `s_auth_rule` WHERE `name` = 'shop/order/index'), `rules`)
  );
