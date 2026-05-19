-- 2026-05-12 商品补充数据，图片来自 public/uploads/20260512
SET NAMES utf8mb4;

UPDATE `s_shop_category` SET
  `image` = CASE `id`
    WHEN 1 THEN '/uploads/20260512/thumb_400x400_2023100900050716920o.jpg'
    WHEN 2 THEN '/uploads/20260512/thumb_400x400_2023100823494816908j.jpg'
    WHEN 3 THEN '/uploads/20260512/thumb_400x400_2023100900304516933k.png'
    WHEN 4 THEN '/uploads/20260512/thumb_400x400_2023101318520116944h.jpg'
    ELSE `image`
  END,
  `updatetime` = UNIX_TIMESTAMP()
WHERE `id` IN (1,2,3,4);

INSERT INTO `s_shop_product`
(`id`,`category_id`,`type`,`sn`,`title`,`subtitle`,`keywords`,`description`,`content`,`main_image`,`price`,`market_price`,`cost_price`,`stock`,`sales`,`is_sku`,`is_recommend`,`is_new`,`is_hot`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(101,3,'normal','SKU-5001','Air Remote Controller','Compact remote controller for smart devices.','remote,smart,controller','Compact remote controller for smart devices.','<p>A lightweight smart controller with clean buttons, stable response and simple daily operation.</p>','/uploads/20260512/thumb_400x400_2023100900050716920o.jpg',49.00,69.00,22.00,188,42,0,1,1,1,200,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(102,3,'normal','SKU-5002','Wireless Gaming Mouse','Responsive wireless mouse with a comfortable grip.','mouse,wireless,gaming','Responsive wireless mouse with a comfortable grip.','<p>Designed for work and entertainment with stable wireless connection and smooth tracking.</p>','/uploads/20260512/01284c06590d3b0a1d3c64ab14b0d722.jpg',39.00,59.00,16.00,220,73,1,1,1,1,198,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(103,3,'normal','SKU-5003','Portable Speaker Mini','Small speaker with clear sound for rooms and travel.','speaker,portable,audio','Small speaker with clear sound for rooms and travel.','<p>Compact body, balanced sound and easy pairing for everyday listening.</p>','/uploads/20260512/thumb_400x400_2023101414142216911v.jpg',59.00,79.00,27.00,160,51,0,1,1,0,196,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(104,1,'normal','SKU-5004','Desktop Charging Dock','A tidy dock for phone and cable organization.','dock,charging,desk','A tidy dock for phone and cable organization.','<p>Keep frequently used devices upright, visible and ready for the next charge.</p>','/uploads/20260512/thumb_400x400_2023100901540416944s.jpg',29.00,39.00,12.00,260,34,0,1,0,1,194,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(105,2,'normal','SKU-5005','Smart LED Night Light','Soft night light for bedside and hallway use.','light,led,home','Soft night light for bedside and hallway use.','<p>Warm low-glare lighting with a compact body for calm home spaces.</p>','/uploads/20260512/thumb_400x400_2023100823494816908j.jpg',19.00,29.00,8.00,300,88,0,0,1,1,192,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(106,2,'normal','SKU-5006','Travel Power Adapter','Compact adapter for charging on the move.','adapter,travel,power','Compact adapter for charging on the move.','<p>A small travel adapter made for daily carry, hotel rooms and backup charging.</p>','/uploads/20260512/5137089fc9b12f3bba355935fc4f64c9.jpg',24.00,36.00,10.00,240,66,1,1,0,0,190,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(107,1,'normal','SKU-5007','Magnetic Desk Holder','Stable magnetic holder for phones and notes.','holder,magnetic,desk','Stable magnetic holder for phones and notes.','<p>Simple desktop holder with a clean angle for calls, notes and quick viewing.</p>','/uploads/20260512/thumb_400x400_2023100901390416925l.jpg',22.00,32.00,9.00,210,31,0,0,1,0,188,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(108,3,'normal','SKU-5008','USB-C Hub Essential','Multi-port hub for laptop and tablet workflows.','hub,usb-c,laptop','Multi-port hub for laptop and tablet workflows.','<p>Expand daily ports with a compact hub built for desk setups and travel bags.</p>','/uploads/20260512/thumb_400x400_2023100900304516933k.png',45.00,65.00,21.00,180,57,1,1,1,1,186,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(109,4,'normal','SKU-5009','Creator Gift Pack','Useful accessories packed for simple gifting.','gift,pack,accessory','Useful accessories packed for simple gifting.','<p>A practical accessory bundle for work, travel and daily device care.</p>','/uploads/20260512/thumb_400x400_2023100901551216922s.jpg',79.00,99.00,38.00,90,24,0,1,0,1,184,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(110,2,'normal','SKU-5010','Everyday Carry Pouch','Soft pouch for cables, cards and small gear.','pouch,carry,travel','Soft pouch for cables, cards and small gear.','<p>Keep small daily items organized with a durable zipper and neat internal space.</p>','/uploads/20260512/thumb_400x400_2023100900332916971f.jpg',18.00,26.00,7.00,320,79,0,0,1,0,182,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(111,3,'normal','SKU-5011','Fast Charge Cable Set','Two charging cables for home and office use.','cable,charge,set','Two charging cables for home and office use.','<p>A dependable cable set for desks, bedside charging and spare travel kits.</p>','/uploads/20260512/thumb_400x400_2023101318520116944h.jpg',16.00,24.00,6.00,420,116,1,1,0,1,180,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(112,1,'normal','SKU-5012','Minimal Desk Clock','Quiet clock for a focused desktop.','clock,desk,workspace','Quiet clock for a focused desktop.','<p>A clean desktop clock with readable numerals and a compact footprint.</p>','/uploads/20260512/thumb_400x400_2023100823455216917b.jpg',35.00,48.00,15.00,170,28,0,0,1,0,178,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(113,2,'normal','SKU-5013','Home Aroma Diffuser','Small diffuser for calm indoor spaces.','home,diffuser,lifestyle','Small diffuser for calm indoor spaces.','<p>A compact diffuser suited to bedrooms, studios and quiet evening routines.</p>','/uploads/20260512/thumb_400x400_2023100900422916973f.jpg',42.00,56.00,19.00,150,43,0,1,1,0,176,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(114,4,'normal','SKU-5014','Premium Accessory Box','A balanced gift box for everyday device care.','gift,box,premium','A balanced gift box for everyday device care.','<p>A premium accessory box with practical items for work, travel and home use.</p>','/uploads/20260512/thumb_400x400_2023101400391516927g.jpg',99.00,129.00,48.00,72,19,0,1,0,1,174,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
  `category_id`=VALUES(`category_id`),
  `title`=VALUES(`title`),
  `subtitle`=VALUES(`subtitle`),
  `description`=VALUES(`description`),
  `content`=VALUES(`content`),
  `main_image`=VALUES(`main_image`),
  `price`=VALUES(`price`),
  `market_price`=VALUES(`market_price`),
  `cost_price`=VALUES(`cost_price`),
  `stock`=VALUES(`stock`),
  `sales`=VALUES(`sales`),
  `is_sku`=VALUES(`is_sku`),
  `is_recommend`=VALUES(`is_recommend`),
  `is_new`=VALUES(`is_new`),
  `is_hot`=VALUES(`is_hot`),
  `weigh`=VALUES(`weigh`),
  `status`=VALUES(`status`),
  `updatetime`=UNIX_TIMESTAMP();

INSERT INTO `s_shop_product_image` (`id`,`product_id`,`image`,`weigh`,`createtime`) VALUES
(101,101,'/uploads/20260512/thumb_400x400_2023100900050716920o.jpg',100,UNIX_TIMESTAMP()),
(102,102,'/uploads/20260512/01284c06590d3b0a1d3c64ab14b0d722.jpg',100,UNIX_TIMESTAMP()),
(103,103,'/uploads/20260512/thumb_400x400_2023101414142216911v.jpg',100,UNIX_TIMESTAMP()),
(104,104,'/uploads/20260512/thumb_400x400_2023100901540416944s.jpg',100,UNIX_TIMESTAMP()),
(105,105,'/uploads/20260512/thumb_400x400_2023100823494816908j.jpg',100,UNIX_TIMESTAMP()),
(106,106,'/uploads/20260512/5137089fc9b12f3bba355935fc4f64c9.jpg',100,UNIX_TIMESTAMP()),
(107,107,'/uploads/20260512/thumb_400x400_2023100901390416925l.jpg',100,UNIX_TIMESTAMP()),
(108,108,'/uploads/20260512/thumb_400x400_2023100900304516933k.png',100,UNIX_TIMESTAMP()),
(109,109,'/uploads/20260512/thumb_400x400_2023100901551216922s.jpg',100,UNIX_TIMESTAMP()),
(110,110,'/uploads/20260512/thumb_400x400_2023100900332916971f.jpg',100,UNIX_TIMESTAMP()),
(111,111,'/uploads/20260512/thumb_400x400_2023101318520116944h.jpg',100,UNIX_TIMESTAMP()),
(112,112,'/uploads/20260512/thumb_400x400_2023100823455216917b.jpg',100,UNIX_TIMESTAMP()),
(113,113,'/uploads/20260512/thumb_400x400_2023100900422916973f.jpg',100,UNIX_TIMESTAMP()),
(114,114,'/uploads/20260512/thumb_400x400_2023101400391516927g.jpg',100,UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `image`=VALUES(`image`),`weigh`=VALUES(`weigh`);

INSERT INTO `s_shop_product_sku`
(`id`,`product_id`,`sku_code`,`spec_json`,`image`,`price`,`market_price`,`stock`,`sales`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(101,102,'SKU-5002-BLACK','Black','/uploads/20260512/01284c06590d3b0a1d3c64ab14b0d722.jpg',39.00,59.00,110,36,100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(102,102,'SKU-5002-WHITE','White','/uploads/20260512/01284c06590d3b0a1d3c64ab14b0d722.jpg',42.00,62.00,110,37,90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(103,106,'SKU-5006-US','US Plug','/uploads/20260512/5137089fc9b12f3bba355935fc4f64c9.jpg',24.00,36.00,120,32,100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(104,106,'SKU-5006-EU','EU Plug','/uploads/20260512/5137089fc9b12f3bba355935fc4f64c9.jpg',26.00,38.00,120,34,90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(105,108,'SKU-5008-GRAY','Space Gray','/uploads/20260512/thumb_400x400_2023100900304516933k.png',45.00,65.00,90,28,100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(106,108,'SKU-5008-SILVER','Silver','/uploads/20260512/thumb_400x400_2023100900304516933k.png',45.00,65.00,90,29,90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(107,111,'SKU-5011-1M','1m Set','/uploads/20260512/thumb_400x400_2023101318520116944h.jpg',16.00,24.00,220,56,100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(108,111,'SKU-5011-2M','2m Set','/uploads/20260512/thumb_400x400_2023101318520116944h.jpg',22.00,32.00,200,60,90,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
  `spec_json`=VALUES(`spec_json`),
  `image`=VALUES(`image`),
  `price`=VALUES(`price`),
  `market_price`=VALUES(`market_price`),
  `stock`=VALUES(`stock`),
  `sales`=VALUES(`sales`),
  `weigh`=VALUES(`weigh`),
  `status`=VALUES(`status`),
  `updatetime`=UNIX_TIMESTAMP();
