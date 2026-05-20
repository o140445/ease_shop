-- FastAdmin 商城演示数据，可重复执行
SET NAMES utf8mb4;

INSERT INTO `s_shop_config` (`id`,`group`,`name`,`title`,`type`,`value`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(1,'basic','site_name','Store name','string','Nova Market',100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,'basic','withdraw_fee_rate','Withdraw fee rate','number','1.00',90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,'basic','site_description','Site description','textarea','Shop Ease is a secure online store for curated electronics, home essentials and lifestyle products, with member pricing, balance payment and reliable after-sales service.',80,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_user_level` (`id`,`name`,`level`,`icon`,`discount_rate`,`min_order_amount`,`min_pay_amount`,`min_recharge_amount`,`description`,`is_default`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(1,'Starter',1,'',100.00,0.00,0.00,0.00,'Default member level',1,100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,'Silver',2,'',95.00,3.00,300.00,300.00,'5% member discount',0,90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,'Gold',3,'',90.00,8.00,1000.00,1000.00,'10% member discount',0,80,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`),`discount_rate`=VALUES(`discount_rate`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_user` (`id`,`level_id`,`username`,`nickname`,`password`,`salt`,`email`,`mobile`,`avatar`,`gender`,`money`,`score`,`total_order_amount`,`total_pay_amount`,`total_recharge_amount`,`joinip`,`jointime`,`status`,`createtime`,`updatetime`) VALUES
(1,2,'demo','Demo Member','','','demo@example.com','13800000000','/assets/img/avatar.png',0,1288.00,120,268.00,268.00,1500.00,'127.0.0.1',UNIX_TIMESTAMP(),'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `nickname`=VALUES(`nickname`),`level_id`=VALUES(`level_id`),`money`=VALUES(`money`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_user_address` (`id`,`user_id`,`consignee`,`mobile`,`country`,`province`,`city`,`district`,`address`,`postal_code`,`is_default`,`createtime`,`updatetime`) VALUES
(1,1,'Demo Member','13800000000','United States','California','San Francisco','Downtown','100 Market Street','94105',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `address`=VALUES(`address`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_user_bank` (`id`,`user_id`,`realname`,`bank_name`,`bank_branch`,`card_no`,`mobile`,`id_card`,`is_default`,`status`,`createtime`,`updatetime`) VALUES
(1,1,'Demo Member','Demo Bank','Market Street Branch','**** **** **** 2468','13800000000','',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `bank_name`=VALUES(`bank_name`),`card_no`=VALUES(`card_no`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_category` (`id`,`parent_id`,`name`,`keywords`,`description`,`weigh`,`image`,`is_nav`,`status`,`createtime`,`updatetime`) VALUES
(1,0,'Workspace','desk,office','Clean tools for better daily work',100,'/assets/img/logo.png',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,0,'Lifestyle','daily,home','Quiet essentials for home and travel',90,'/assets/img/logo.png',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,0,'Digital','device,accessory','Useful digital accessories',80,'/assets/img/logo.png',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(4,0,'Gift sets','gift,bundle','Curated bundles for easy gifting',70,'/assets/img/logo.png',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`),`description`=VALUES(`description`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_product` (`id`,`category_id`,`type`,`sn`,`title`,`subtitle`,`keywords`,`description`,`content`,`main_image`,`price`,`market_price`,`cost_price`,`stock`,`sales`,`is_sku`,`is_recommend`,`is_new`,`is_hot`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(1,1,'normal','SKU-1001','Modular Desk Tray','A compact organizer for cables, notes and daily tools.','desk,organizer','A compact organizer for focused work.','<p>The Modular Desk Tray keeps everyday tools in one calm, visible place. Built for repeat use and easy cleaning.</p>','/assets/img/logo.png',39.00,49.00,18.00,120,36,1,1,1,1,100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,1,'normal','SKU-1002','Soft Task Lamp','Warm desk lighting with a small footprint.','lamp,desk','Warm lighting for long sessions.','<p>A soft task lamp designed for evening focus, reading and relaxed desktop setups.</p>','/assets/img/logo.png',68.00,89.00,32.00,80,18,0,1,1,0,95,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,2,'normal','SKU-2001','Travel Tumbler','A sealed tumbler for coffee, tea and cold drinks.','travel,tumbler','A reliable cup for daily commutes.','<p>Double-wall construction keeps drinks comfortable while the sealed lid travels cleanly.</p>','/assets/img/logo.png',24.00,32.00,9.00,200,64,0,1,0,1,90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(4,2,'normal','SKU-2002','Canvas Utility Pouch','A simple pouch for chargers and small essentials.','pouch,travel','A flexible pouch for daily carry.','<p>Durable canvas, smooth zipper and enough structure to find small items quickly.</p>','/assets/img/logo.png',18.00,25.00,7.00,160,42,0,0,1,0,80,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(5,3,'normal','SKU-3001','Braided USB-C Cable','Durable charging cable with a tidy finish.','usb,cable','A dependable cable for desk and travel.','<p>A braided cable with reinforced ends, made for daily charging and data transfer.</p>','/assets/img/logo.png',12.00,18.00,4.00,300,91,1,1,0,1,85,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(6,3,'normal','SKU-3002','Magnetic Phone Stand','Compact stand for calls, recipes and video.','phone,stand','A stable stand with a small desktop footprint.','<p>Weighted base, adjustable angle and a clean magnetic mount for supported devices.</p>','/assets/img/logo.png',29.00,39.00,12.00,100,22,0,0,1,0,75,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(7,4,'normal','SKU-4001','Focus Starter Kit','Desk tray, pouch and cable in one bundle.','gift,bundle','A practical starter kit for workspaces.','<p>A curated gift set for anyone refreshing a desk, studio or remote work setup.</p>','/assets/img/logo.png',59.00,78.00,28.00,60,15,0,1,1,1,110,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(8,4,'virtual','SKU-4002','Digital Setup Guide','A digital guide for creating a calm workspace.','guide,digital','A downloadable workspace planning guide.','<p>This virtual product includes layout ideas, cable planning and weekly reset rituals.</p>','/assets/img/logo.png',9.00,15.00,0.00,999,120,0,0,1,1,70,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`),`price`=VALUES(`price`),`stock`=VALUES(`stock`),`status`=VALUES(`status`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_product_sku` (`id`,`product_id`,`sku_code`,`spec_json`,`image`,`price`,`market_price`,`stock`,`sales`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(1,1,'SKU-1001-OAK','Oak / Small','/assets/img/logo.png',39.00,49.00,60,18,100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,1,'SKU-1001-WALNUT','Walnut / Small','/assets/img/logo.png',42.00,52.00,60,18,90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,5,'SKU-3001-1M','1m / Graphite','/assets/img/logo.png',12.00,18.00,180,51,100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(4,5,'SKU-3001-2M','2m / Graphite','/assets/img/logo.png',16.00,22.00,120,40,90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `spec_json`=VALUES(`spec_json`),`price`=VALUES(`price`),`stock`=VALUES(`stock`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_product_image` (`id`,`product_id`,`image`,`weigh`,`createtime`) VALUES
(1,1,'/assets/img/logo.png',100,UNIX_TIMESTAMP()),
(2,2,'/assets/img/logo.png',100,UNIX_TIMESTAMP()),
(3,3,'/assets/img/logo.png',100,UNIX_TIMESTAMP()),
(4,7,'/assets/img/logo.png',100,UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `image`=VALUES(`image`);

INSERT INTO `s_shop_banner` (`id`,`position`,`title`,`subtitle`,`image`,`link_type`,`link_url`,`link_id`,`target`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(1,'home','Tools for a calmer daily rhythm','Curated goods for work, travel and home, paid with account balance.','/assets/img/logo.png','url','',0,'self',100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`),`subtitle`=VALUES(`subtitle`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_nav` (`id`,`parent_id`,`position`,`title`,`icon`,`image`,`link_type`,`link_url`,`link_id`,`target`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(1,0,'home','Home','','','url','',0,'self',100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,0,'home','Products','','','url','',0,'self',90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,0,'home','Cart','','','url','',0,'self',80,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(4,0,'home','Member center','','','url','',0,'self',70,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`),`weigh`=VALUES(`weigh`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_notice` (`id`,`type`,`title`,`summary`,`content`,`cover_image`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(1,'notice','Balance payment is now enabled','All demo products can be checked out with account balance.','<p>The storefront now supports balance-based shopping workflows. Order creation can be connected after product selection and cart confirmation.</p>','',100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,'help','How member discounts work','Discount rates are stored on member levels and snapshotted into orders.','<p>When an order is created, the current member level and discount rate should be saved with the order for historical accuracy.</p>','',90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`),`summary`=VALUES(`summary`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_recharge` (`id`,`recharge_no`,`user_id`,`money`,`give_money`,`pay_money`,`pay_type`,`pay_status`,`voucher`,`remark`,`admin_id`,`admin_remark`,`paidtime`,`createtime`,`updatetime`) VALUES
(1,'RC202605110001',1,1500.00,0.00,1500.00,'admin','paid','','Seed recharge',1,'Demo seed data',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `money`=VALUES(`money`),`pay_status`=VALUES(`pay_status`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_order` (`id`,`order_no`,`user_id`,`status`,`pay_type`,`pay_status`,`product_amount`,`freight_amount`,`level_id`,`level_name`,`level_discount_rate`,`level_discount_amount`,`discount_amount`,`pay_amount`,`total_quantity`,`receiver_name`,`receiver_mobile`,`receiver_country`,`receiver_province`,`receiver_city`,`receiver_district`,`receiver_address`,`receiver_postal_code`,`remark`,`admin_remark`,`paidtime`,`shiptime`,`completetime`,`createtime`,`updatetime`) VALUES
(1,'OD202605110001',1,'completed','balance','paid',268.00,0.00,2,'Silver',95.00,13.40,13.40,254.60,4,'Demo Member','13800000000','United States','California','San Francisco','Downtown','100 Market Street','94105','','Seed order',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `status`=VALUES(`status`),`pay_amount`=VALUES(`pay_amount`),`updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_order_item` (`id`,`order_id`,`order_no`,`user_id`,`product_id`,`sku_id`,`product_sn`,`sku_code`,`title`,`sku_spec`,`image`,`price`,`quantity`,`total_price`,`refund_status`,`createtime`) VALUES
(1,1,'OD202605110001',1,1,1,'SKU-1001','SKU-1001-OAK','Modular Desk Tray','Oak / Small','/assets/img/logo.png',39.00,2,78.00,'none',UNIX_TIMESTAMP()),
(2,1,'OD202605110001',1,7,0,'SKU-4001','','Focus Starter Kit','','/assets/img/logo.png',59.00,1,59.00,'none',UNIX_TIMESTAMP()),
(3,1,'OD202605110001',1,3,0,'SKU-2001','','Travel Tumbler','','/assets/img/logo.png',24.00,1,24.00,'none',UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`),`quantity`=VALUES(`quantity`),`total_price`=VALUES(`total_price`);

INSERT INTO `s_shop_balance_log` (`id`,`user_id`,`type`,`order_id`,`recharge_id`,`withdraw_id`,`refund_id`,`money`,`before`,`after`,`memo`,`createtime`) VALUES
(1,1,'recharge',0,1,0,0,1500.00,0.00,1500.00,'Demo recharge',UNIX_TIMESTAMP()),
(2,1,'pay',1,0,0,0,-254.60,1500.00,1245.40,'Demo order payment',UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `money`=VALUES(`money`),`after`=VALUES(`after`);

INSERT INTO `s_shop_cart` (`id`,`user_id`,`product_id`,`sku_id`,`quantity`,`selected`,`createtime`,`updatetime`) VALUES
(1,1,2,0,1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,1,5,4,2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `quantity`=VALUES(`quantity`),`updatetime`=UNIX_TIMESTAMP();
