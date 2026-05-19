ALTER TABLE `s_shop_order`
  MODIFY COLUMN `status` enum('unpaid','paid','shipped','completed','returned','cancelled','refunding','refunded','recycled') NOT NULL DEFAULT 'unpaid' COMMENT '订单状态:unpaid=待支付,paid=待发货,shipped=待收货,completed=已完成,returned=已退货,cancelled=已取消,refunding=退款中,refunded=已退款,recycled=已回收';

ALTER TABLE `s_shop_order`
  MODIFY COLUMN `pay_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid' COMMENT '支付状态:unpaid=未支付,paid=已支付,refunded=已退款';
