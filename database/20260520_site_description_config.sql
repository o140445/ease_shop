INSERT INTO `s_config` (`name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `setting`)
VALUES (
  'site_description',
  'basic',
  'Site description',
  '',
  'text',
  'Shop Ease is a secure online store for curated electronics, home essentials and lifestyle products with member pricing and balance payment.',
  '',
  '',
  '',
  ''
)
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

INSERT INTO `s_shop_config` (`group`, `name`, `title`, `type`, `value`, `weigh`, `status`, `createtime`, `updatetime`)
VALUES (
  'basic',
  'site_description',
  'Site description',
  'textarea',
  'Shop Ease is a secure online store for curated electronics, home essentials and lifestyle products with member pricing and balance payment.',
  80,
  'normal',
  UNIX_TIMESTAMP(),
  UNIX_TIMESTAMP()
)
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updatetime` = VALUES(`updatetime`);
