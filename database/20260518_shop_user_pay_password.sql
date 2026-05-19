ALTER TABLE `s_shop_user`
  ADD COLUMN `pay_password` varchar(32) NOT NULL DEFAULT '' COMMENT '支付密码' AFTER `status`,
  ADD COLUMN `pay_salt` varchar(30) NOT NULL DEFAULT '' COMMENT '支付密码盐' AFTER `pay_password`;
