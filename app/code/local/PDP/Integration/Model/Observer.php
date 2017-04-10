<?php
/**
 * Magento Support Team.
 * @category   MST
 * @package    MST_Pdp
 * @version    2.0
 * @author     Magebay Developer Team <info@magebay.com>
 * @copyright  Copyright (c) 2009-2013 MAGEBAY.COM. (http://www.magebay.com)
 */
class PDP_Integration_Model_Observer extends Varien_Object
{
    public function catalogProductLoadAfter(Varien_Event_Observer $observer)
    {
        // set the additional options on the product
        $action = Mage::app()->getFrontController()->getAction();
		$actionName = "";
		try {
			if($action) {
				$actionName = $action->getFullActionName();
			}
		} catch(Exception $e) {
			$actionName = "";
		}
		//echo $actionName;
        if ($actionName == 'integration_index_index')
        {
            // assuming you are posting your custom form values in an array called extra_options...
            $extraOption = $action->getRequest()->getParam('extra_options');
            if ($extraOption != "")
            {
                $product = $observer->getProduct();
                // add to the additional options array
                $additionalOptions = array();
                if ($additionalOption = $product->getCustomOption('additional_options'))
                {
                    $additionalOptions = (array) unserialize($additionalOption->getValue());
                }
                $additionalOptions[] = array(
                    'code' => 'pdpinfo',
                    'label' => '',
                    'value' => '',
                    'json' => 'Mai uoc',
                	'time' => microtime()
                );
                // add the additional options array with the option code additional_options
                $observer->getProduct()
                    ->addCustomOption('additional_options', serialize($additionalOptions));
            }
        }
    }
	public function changePrice($observer) 
	{	
		$productPrice = $observer->getProduct()->getFinalPrice();
		$pricePdp = 10;
		$finalPrice = $pricePdp + $productPrice;
		$item = $observer->getQuoteItem();
		// Ensure we have the parent item, if it has one
		$item = ($item->getParentItem() ? $item->getParentItem() : $item);
		$item->setCustomPrice($finalPrice);
		$item->setOriginalCustomPrice($finalPrice);
		// Enable super mode on the product.
		$item->getProduct()->setIsSuperMode(true);
    }
}