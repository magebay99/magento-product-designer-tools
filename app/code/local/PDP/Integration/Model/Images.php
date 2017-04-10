<?php
Class PDP_Integration_Model_Images extends Mage_Core_Model_Abstract
{
	function maintain()
	{
		$currentDate = Mage::getModel('core/date')->date('Y-m-d');
		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect(array('entity_id'))
			->addAttributeToFilter('cron_stock_date',$currentDate);
		$productIds1 = $collection->getAllIds();
		if(count($productIds1))
		{
			foreach($productIds1 as $productId1)
			{
				
				$stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId1);
				if($stock_item->getIsInStock() == 1)
				{
				}
				else
				{
					//change stock status
					if (!$stock_item->getId()) {
					$stock_item->setData('product_id', $productId1);
					$stock_item->setData('stock_id', 1); 
					}

					$stock_item->setData('is_in_stock', 1); 
					$stock_item->setData('manage_stock', 1); // should be 1 to make something out of stock

					try {
						$stock_item->save();
					} catch (Exception $e) {
						
					}
				}
				
			}
		}
		//task 2
		//empty cron date
		$collection2 = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect(array('entity_id'))
			->addAttributeToFilter('cron_text_date',$currentDate);
		$productIds2 = $collection2->getAllIds();
		if(count($productIds2))
		{
			foreach($productIds2 as $productId2)
			{
				try {
						$_product = Mage::getModel('catalog/product')->load($productId2);
						$_product->setCronTextDate('');
						$_product->setCronText('');
						$_product->save();
					} catch (Exception $e) {
						
					}
				
			}
		}
	}
	
}