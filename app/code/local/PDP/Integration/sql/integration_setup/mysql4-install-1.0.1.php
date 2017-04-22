<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('integration/customerdesigns')} (
  `integration_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `design_id` int(11) NOT NULL,
  `mage_product_id` int(11) NOT NULL,
  `mage_product_sku` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `thumbmail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `price` float NOT NULL,
  `pdp_product_id` int(11) NOT NULL,
  PRIMARY KEY (`integration_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
");
$installer->endSetup(); 