ALTER TABLE `s_shop_user`
  ADD COLUMN `frozen_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结余额' AFTER `money`;
