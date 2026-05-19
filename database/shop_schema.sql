-- FastAdmin 商城业务库表
-- 说明：
-- 1. 商城会员使用独立表 s_shop_user。
-- 2. 时间字段沿用 FastAdmin 习惯：createtime/updatetime/deletetime，类型为 int(10) Unix 时间戳。
-- 3. 前端多语言由翻译引擎处理，业务库只保存默认语言内容。

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 商品分类
-- ----------------------------
CREATE TABLE IF NOT EXISTS `s_shop_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO关键词',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '分类图片',
  `is_nav` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否导航显示:0=否,1=是',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=隐藏',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_weigh` (`weigh`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-商品分类';

-- ----------------------------
-- 商品
-- ----------------------------
CREATE TABLE IF NOT EXISTS `s_shop_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `type` enum('normal','virtual') NOT NULL DEFAULT 'normal' COMMENT '商品类型:normal=实物,virtual=虚拟',
  `sn` varchar(64) NOT NULL DEFAULT '' COMMENT '商品编码',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '商品标题',
  `subtitle` varchar(255) NOT NULL DEFAULT '' COMMENT '副标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO关键词',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  `content` longtext COMMENT '商品详情',
  `main_image` varchar(255) NOT NULL DEFAULT '' COMMENT '主图',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '销售价',
  `market_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '市场价',
  `cost_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成本价',
  `stock` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总库存',
  `sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `is_sku` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否多规格:0=否,1=是',
  `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐:0=否,1=是',
  `is_new` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否新品:0=否,1=是',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否热卖:0=否,1=是',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden','soldout') NOT NULL DEFAULT 'hidden' COMMENT '状态:normal=上架,hidden=下架,soldout=售罄',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sn` (`sn`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_title` (`title`),
  KEY `idx_status_weigh` (`status`,`weigh`),
  KEY `idx_flags` (`is_recommend`,`is_new`,`is_hot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-商品';

CREATE TABLE IF NOT EXISTS `s_shop_product_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_product_weigh` (`product_id`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-商品图片';

CREATE TABLE IF NOT EXISTS `s_shop_product_sku` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `sku_code` varchar(64) NOT NULL DEFAULT '' COMMENT 'SKU编码',
  `spec_json` varchar(1000) NOT NULL DEFAULT '' COMMENT '规格JSON',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT 'SKU图片',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '销售价',
  `market_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '市场价',
  `stock` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  `sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=禁用',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sku_code` (`sku_code`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-商品SKU';

CREATE TABLE IF NOT EXISTS `s_shop_cart` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `sku_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'SKU ID',
  `quantity` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数量',
  `selected` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否选中:0=否,1=是',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_product_sku` (`user_id`,`product_id`,`sku_id`),
  KEY `idx_user_selected` (`user_id`,`selected`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-购物车';

CREATE TABLE IF NOT EXISTS `s_shop_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_product` (`user_id`,`product_id`),
  KEY `idx_user_time` (`user_id`,`createtime`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-商品收藏';

-- ----------------------------
-- 用户
-- ----------------------------
CREATE TABLE IF NOT EXISTS `s_shop_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `level_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员等级ID',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '手机号',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别:0=未知,1=男,2=女',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `frozen_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结余额',
  `score` int(10) NOT NULL DEFAULT '0' COMMENT '积分',
  `total_order_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '累计订单金额',
  `total_pay_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '累计消费金额',
  `total_recharge_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '累计充值金额',
  `successions` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '连续登录天数',
  `maxsuccessions` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '最大连续登录天数',
  `prevtime` int(10) unsigned DEFAULT NULL COMMENT '上次登录时间',
  `logintime` int(10) unsigned DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `joinip` varchar(50) NOT NULL DEFAULT '' COMMENT '加入IP',
  `jointime` int(10) unsigned DEFAULT NULL COMMENT '加入时间',
  `token` varchar(50) NOT NULL DEFAULT '' COMMENT 'Token',
  `status` enum('normal','hidden','locked') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=禁用,locked=锁定',
  `pay_password` varchar(32) NOT NULL DEFAULT '' COMMENT '支付密码',
  `pay_salt` varchar(30) NOT NULL DEFAULT '' COMMENT '支付密码盐',
  `verification` varchar(255) NOT NULL DEFAULT '' COMMENT '验证',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_email` (`email`),
  KEY `idx_level_id` (`level_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-会员';

CREATE TABLE IF NOT EXISTS `s_shop_user_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `consignee` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人',
  `mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '手机号',
  `country` varchar(100) NOT NULL DEFAULT '' COMMENT '国家/地区',
  `province` varchar(100) NOT NULL DEFAULT '' COMMENT '省/州',
  `city` varchar(100) NOT NULL DEFAULT '' COMMENT '城市',
  `district` varchar(100) NOT NULL DEFAULT '' COMMENT '区县',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '详细地址',
  `postal_code` varchar(30) NOT NULL DEFAULT '' COMMENT '邮编',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认:0=否,1=是',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_default` (`user_id`,`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-用户收货地址';

CREATE TABLE IF NOT EXISTS `s_shop_user_bank` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `realname` varchar(80) NOT NULL DEFAULT '' COMMENT '持卡人姓名',
  `bank_name` varchar(120) NOT NULL DEFAULT '' COMMENT '银行名称',
  `bank_branch` varchar(200) NOT NULL DEFAULT '' COMMENT '开户地址',
  `card_no` varchar(80) NOT NULL DEFAULT '' COMMENT '银行卡号',
  `mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '预留手机号',
  `id_card` varchar(50) NOT NULL DEFAULT '' COMMENT '身份证号',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认:0=否,1=是',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=禁用',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_default` (`user_id`,`is_default`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-用户银行卡';

CREATE TABLE IF NOT EXISTS `s_shop_user_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '等级名称',
  `level` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '等级值',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '等级图标',
  `discount_rate` decimal(5,2) unsigned NOT NULL DEFAULT '100.00' COMMENT '折扣率百分比',
  `min_order_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '升级订单金额',
  `min_pay_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '升级消费金额',
  `min_recharge_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '升级充值金额',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '等级说明',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认:0=否,1=是',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=禁用',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_level` (`level`),
  KEY `idx_default` (`is_default`),
  KEY `idx_status_weigh` (`status`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-会员等级';

CREATE TABLE IF NOT EXISTS `s_shop_balance_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `type` enum('recharge','pay','refund','recycle','withdraw','withdraw_reject','freeze','unfreeze','adjust') NOT NULL DEFAULT 'adjust' COMMENT '类型:recharge=充值,pay=支付,refund=退款,recycle=订单回收,withdraw=提款,withdraw_reject=提款驳回,freeze=冻结金额,unfreeze=解冻金额,adjust=调整',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `recharge_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '充值ID',
  `withdraw_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提款ID',
  `refund_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退款ID',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动金额',
  `before` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动前',
  `after` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动后',
  `memo` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_time` (`user_id`,`createtime`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_withdraw_id` (`withdraw_id`),
  KEY `idx_refund_id` (`refund_id`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-余额流水';

CREATE TABLE IF NOT EXISTS `s_shop_recharge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `recharge_no` varchar(64) NOT NULL COMMENT '充值单号',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `give_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '赠送金额',
  `pay_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '应付金额',
  `pay_type` enum('offline','admin') NOT NULL DEFAULT 'offline' COMMENT '充值方式:offline=线下充值,admin=后台充值',
  `pay_status` enum('unpaid','paid','cancelled') NOT NULL DEFAULT 'unpaid' COMMENT '支付状态:unpaid=未到账,paid=已到账,cancelled=已取消',
  `voucher` varchar(255) NOT NULL DEFAULT '' COMMENT '充值凭证',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '用户备注',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `admin_remark` varchar(500) NOT NULL DEFAULT '' COMMENT '后台备注',
  `paidtime` int(10) unsigned DEFAULT NULL COMMENT '到账时间',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_recharge_no` (`recharge_no`),
  KEY `idx_user_time` (`user_id`,`createtime`),
  KEY `idx_pay_status` (`pay_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-余额充值';

CREATE TABLE IF NOT EXISTS `s_shop_withdraw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `withdraw_no` varchar(64) NOT NULL COMMENT '提款单号',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `bank_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户银行卡ID',
  `realname` varchar(80) NOT NULL DEFAULT '' COMMENT '持卡人姓名快照',
  `card_no` varchar(80) NOT NULL DEFAULT '' COMMENT '银行卡号快照',
  `bank_name` varchar(120) NOT NULL DEFAULT '' COMMENT '银行名称快照',
  `bank_branch` varchar(200) NOT NULL DEFAULT '' COMMENT '开户地址快照',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '提款金额',
  `fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `actual_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际到账',
  `status` enum('pending','approved','rejected','paid','cancelled') NOT NULL DEFAULT 'pending' COMMENT '状态:pending=待审核,approved=已通过,rejected=已驳回,paid=已打款,cancelled=已取消',
  `audit_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核管理员ID',
  `audit_remark` varchar(500) NOT NULL DEFAULT '' COMMENT '审核备注',
  `paid_voucher` varchar(255) NOT NULL DEFAULT '' COMMENT '打款凭证',
  `applytime` int(10) unsigned DEFAULT NULL COMMENT '申请时间',
  `audittime` int(10) unsigned DEFAULT NULL COMMENT '审核时间',
  `paidtime` int(10) unsigned DEFAULT NULL COMMENT '打款时间',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_withdraw_no` (`withdraw_no`),
  KEY `idx_user_time` (`user_id`,`createtime`),
  KEY `idx_status` (`status`),
  KEY `idx_bank_id` (`bank_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-提款申请';

-- ----------------------------
-- 订单
-- ----------------------------
CREATE TABLE IF NOT EXISTS `s_shop_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_no` varchar(64) NOT NULL COMMENT '订单号',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `status` enum('unpaid','paid','shipped','completed','returned','cancelled','refunding','refunded','recycled') NOT NULL DEFAULT 'unpaid' COMMENT '订单状态:unpaid=待支付,paid=待发货,shipped=待收货,completed=已完成,returned=已退货,cancelled=已取消,refunding=退款中,refunded=已退款,recycled=已回收',
  `pay_type` enum('balance') NOT NULL DEFAULT 'balance' COMMENT '支付方式:balance=余额支付',
  `pay_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid' COMMENT '支付状态:unpaid=未支付,paid=已支付,refunded=已退款',
  `product_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '商品金额',
  `freight_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '运费',
  `level_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员等级ID快照',
  `level_name` varchar(100) NOT NULL DEFAULT '' COMMENT '会员等级名称快照',
  `level_discount_rate` decimal(5,2) unsigned NOT NULL DEFAULT '100.00' COMMENT '会员折扣率快照',
  `level_discount_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '会员折扣金额',
  `discount_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
  `pay_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实付金额',
  `total_quantity` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品数量',
  `receiver_name` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人',
  `receiver_mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '收货电话',
  `receiver_country` varchar(100) NOT NULL DEFAULT '' COMMENT '国家/地区',
  `receiver_province` varchar(100) NOT NULL DEFAULT '' COMMENT '省/州',
  `receiver_city` varchar(100) NOT NULL DEFAULT '' COMMENT '城市',
  `receiver_district` varchar(100) NOT NULL DEFAULT '' COMMENT '区县',
  `receiver_address` varchar(255) NOT NULL DEFAULT '' COMMENT '详细地址',
  `receiver_postal_code` varchar(30) NOT NULL DEFAULT '' COMMENT '邮编',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '买家备注',
  `admin_remark` varchar(500) NOT NULL DEFAULT '' COMMENT '后台备注',
  `paidtime` int(10) unsigned DEFAULT NULL COMMENT '支付时间',
  `shiptime` int(10) unsigned DEFAULT NULL COMMENT '发货时间',
  `completetime` int(10) unsigned DEFAULT NULL COMMENT '完成时间',
  `canceltime` int(10) unsigned DEFAULT NULL COMMENT '取消时间',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user_time` (`user_id`,`createtime`),
  KEY `idx_status` (`status`),
  KEY `idx_pay_status` (`pay_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-订单';

CREATE TABLE IF NOT EXISTS `s_shop_order_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单ID',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `sku_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'SKU ID',
  `product_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '商品编码快照',
  `sku_code` varchar(64) NOT NULL DEFAULT '' COMMENT 'SKU编码快照',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '商品标题快照',
  `sku_spec` varchar(1000) NOT NULL DEFAULT '' COMMENT '规格快照',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '商品图片快照',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成交单价',
  `quantity` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '购买数量',
  `total_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '小计',
  `refund_status` enum('none','applying','agreed','rejected','refunded') NOT NULL DEFAULT 'none' COMMENT '退款状态:none=无,applying=申请中,agreed=同意,rejected=拒绝,refunded=已退款',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-订单商品';

CREATE TABLE IF NOT EXISTS `s_shop_order_delivery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `delivery_no` varchar(64) NOT NULL DEFAULT '' COMMENT '发货单号',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单ID',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `delivery_type` enum('express','manual','virtual') NOT NULL DEFAULT 'manual' COMMENT '发货方式:express=物流,manual=无需物流,virtual=虚拟发货',
  `express_company` varchar(100) NOT NULL DEFAULT '' COMMENT '物流公司',
  `express_no` varchar(100) NOT NULL DEFAULT '' COMMENT '物流单号',
  `receiver_name` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人快照',
  `receiver_mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '收货电话快照',
  `receiver_address` varchar(500) NOT NULL DEFAULT '' COMMENT '收货地址快照',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '发货备注',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发货管理员ID',
  `status` enum('shipped','received','cancelled') NOT NULL DEFAULT 'shipped' COMMENT '状态:shipped=已发货,received=已收货,cancelled=已取消',
  `shiptime` int(10) unsigned DEFAULT NULL COMMENT '发货时间',
  `receivetime` int(10) unsigned DEFAULT NULL COMMENT '收货时间',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_user_time` (`user_id`,`shiptime`),
  KEY `idx_express_no` (`express_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-订单发货';

CREATE TABLE IF NOT EXISTS `s_shop_refund` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `refund_no` varchar(64) NOT NULL COMMENT '退款单号',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单ID',
  `order_item_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单商品ID',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `sku_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'SKU ID',
  `type` enum('refund','return_refund') NOT NULL DEFAULT 'refund' COMMENT '售后类型:refund=仅退款,return_refund=退货退款',
  `reason` varchar(255) NOT NULL DEFAULT '' COMMENT '退款原因',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '问题描述',
  `images` varchar(2000) NOT NULL DEFAULT '' COMMENT '凭证图片',
  `apply_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '申请退款金额',
  `refund_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际退款金额',
  `quantity` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '退款数量',
  `status` enum('pending','approved','rejected','returned','refunded','cancelled') NOT NULL DEFAULT 'pending' COMMENT '状态:pending=待审核,approved=已通过,rejected=已拒绝,returned=已退货,refunded=已退款,cancelled=已取消',
  `return_express_company` varchar(100) NOT NULL DEFAULT '' COMMENT '退货物流公司',
  `return_express_no` varchar(100) NOT NULL DEFAULT '' COMMENT '退货物流单号',
  `audit_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核管理员ID',
  `audit_remark` varchar(500) NOT NULL DEFAULT '' COMMENT '审核备注',
  `refund_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退款管理员ID',
  `refund_remark` varchar(500) NOT NULL DEFAULT '' COMMENT '退款备注',
  `applytime` int(10) unsigned DEFAULT NULL COMMENT '申请时间',
  `audittime` int(10) unsigned DEFAULT NULL COMMENT '审核时间',
  `returntime` int(10) unsigned DEFAULT NULL COMMENT '退货时间',
  `refundtime` int(10) unsigned DEFAULT NULL COMMENT '退款时间',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_refund_no` (`refund_no`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_order_item_id` (`order_item_id`),
  KEY `idx_user_time` (`user_id`,`createtime`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-售后退款';

CREATE TABLE IF NOT EXISTS `s_shop_order_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单ID',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `action` varchar(50) NOT NULL DEFAULT '' COMMENT '操作',
  `from_status` varchar(30) NOT NULL DEFAULT '' COMMENT '原状态',
  `to_status` varchar(30) NOT NULL DEFAULT '' COMMENT '新状态',
  `memo` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_time` (`order_id`,`createtime`),
  KEY `idx_admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-订单日志';

CREATE TABLE IF NOT EXISTS `s_shop_order_recycle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单ID',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `order_status` varchar(30) NOT NULL DEFAULT '' COMMENT '订单状态快照',
  `pay_status` varchar(30) NOT NULL DEFAULT '' COMMENT '支付状态快照',
  `pay_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实付金额快照',
  `recycle_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '回收金额',
  `total_quantity` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品数量快照',
  `receiver_name` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人快照',
  `receiver_mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '收货电话快照',
  `receiver_address` varchar(500) NOT NULL DEFAULT '' COMMENT '收货地址快照',
  `status` enum('pending','approved','rejected','recycled','restored','deleted') NOT NULL DEFAULT 'pending' COMMENT '状态:pending=待审核,approved=已通过,rejected=已拒绝,recycled=已回收,restored=已恢复,deleted=已彻底删除',
  `recycle_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回收管理员ID',
  `audit_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核管理员ID',
  `restore_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '恢复管理员ID',
  `delete_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除管理员ID',
  `memo` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `recycletime` int(10) unsigned DEFAULT NULL COMMENT '回收时间',
  `audittime` int(10) unsigned DEFAULT NULL COMMENT '审核时间',
  `restoretime` int(10) unsigned DEFAULT NULL COMMENT '恢复时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '彻底删除时间',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_id` (`order_id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_user_time` (`user_id`,`recycletime`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-订单回收';

CREATE TABLE IF NOT EXISTS `s_shop_order_recycle_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `recycle_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单回收ID',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单ID',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `action` enum('recycle','approve','reject','restore','delete') NOT NULL DEFAULT 'recycle' COMMENT '操作:recycle=提交回收,approve=审核通过,reject=审核拒绝,restore=恢复,delete=彻底删除',
  `memo` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_recycle_id` (`recycle_id`),
  KEY `idx_order_time` (`order_id`,`createtime`),
  KEY `idx_action` (`action`),
  KEY `idx_admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-订单回收日志';

-- ----------------------------
-- 公告
-- ----------------------------
CREATE TABLE IF NOT EXISTS `s_shop_notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` enum('notice','help','agreement') NOT NULL DEFAULT 'notice' COMMENT '类型:notice=公告,help=帮助,agreement=协议',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `summary` varchar(500) NOT NULL DEFAULT '' COMMENT '摘要',
  `content` longtext COMMENT '内容',
  `cover_image` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=隐藏',
  `starttime` int(10) unsigned DEFAULT NULL COMMENT '开始时间',
  `endtime` int(10) unsigned DEFAULT NULL COMMENT '结束时间',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`),
  KEY `idx_status_time` (`status`,`starttime`,`endtime`),
  KEY `idx_type_weigh` (`type`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-公告';

-- ----------------------------
-- 运营配置
-- ----------------------------
CREATE TABLE IF NOT EXISTS `s_shop_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group` varchar(50) NOT NULL DEFAULT 'basic' COMMENT '分组',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '配置名',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '配置标题',
  `type` enum('string','number','textarea','image','switch','select','json') NOT NULL DEFAULT 'string' COMMENT '类型:string=文本,number=数字,textarea=多行文本,image=图片,switch=开关,select=选项,json=JSON',
  `value` text COMMENT '配置值',
  `options` text COMMENT '选项JSON',
  `tip` varchar(255) NOT NULL DEFAULT '' COMMENT '提示',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=隐藏',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_name` (`group`,`name`),
  KEY `idx_group_weigh` (`group`,`weigh`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-配置';

CREATE TABLE IF NOT EXISTS `s_shop_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `position` varchar(50) NOT NULL DEFAULT 'home' COMMENT '位置:home=首页',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `subtitle` varchar(255) NOT NULL DEFAULT '' COMMENT '副标题',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `link_type` enum('none','url','product','category','notice') NOT NULL DEFAULT 'none' COMMENT '链接类型:none=无,url=链接,product=商品,category=分类,notice=公告',
  `link_url` varchar(500) NOT NULL DEFAULT '' COMMENT '链接地址',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
  `target` enum('self','blank') NOT NULL DEFAULT 'self' COMMENT '打开方式:self=当前页,blank=新窗口',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=隐藏',
  `starttime` int(10) unsigned DEFAULT NULL COMMENT '开始时间',
  `endtime` int(10) unsigned DEFAULT NULL COMMENT '结束时间',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_position_weigh` (`position`,`weigh`),
  KEY `idx_status_time` (`status`,`starttime`,`endtime`),
  KEY `idx_link` (`link_type`,`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-首页Banner';

CREATE TABLE IF NOT EXISTS `s_shop_nav` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `position` varchar(50) NOT NULL DEFAULT 'home' COMMENT '位置:home=首页,footer=底部,user=会员中心',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '导航标题',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '图标',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `link_type` enum('url','product','category','notice','page') NOT NULL DEFAULT 'url' COMMENT '链接类型:url=链接,product=商品,category=分类,notice=公告,page=页面',
  `link_url` varchar(500) NOT NULL DEFAULT '' COMMENT '链接地址',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
  `target` enum('self','blank') NOT NULL DEFAULT 'self' COMMENT '打开方式:self=当前页,blank=新窗口',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=隐藏',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_position_weigh` (`position`,`weigh`),
  KEY `idx_status` (`status`),
  KEY `idx_link` (`link_type`,`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-导航';

CREATE TABLE IF NOT EXISTS `s_shop_home_module` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '模块编码',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '模块标题',
  `type` enum('product','category','notice','custom') NOT NULL DEFAULT 'product' COMMENT '模块类型:product=商品,category=分类,notice=公告,custom=自定义',
  `style` varchar(50) NOT NULL DEFAULT 'grid' COMMENT '展示样式',
  `data_type` enum('auto','manual') NOT NULL DEFAULT 'manual' COMMENT '数据来源:auto=自动,manual=手动',
  `ref_ids` varchar(1000) NOT NULL DEFAULT '' COMMENT '手动关联ID',
  `limit_num` int(10) unsigned NOT NULL DEFAULT '10' COMMENT '显示数量',
  `more_url` varchar(500) NOT NULL DEFAULT '' COMMENT '更多链接',
  `extra` text COMMENT '扩展JSON',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,hidden=隐藏',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_type_weigh` (`type`,`weigh`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城-首页模块';

SET FOREIGN_KEY_CHECKS = 1;
