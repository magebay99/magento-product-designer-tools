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
		
        if ($actionName == 'integration_cart_add')
        {
			$pdpCartData = Mage::getSingleton('core/session')->getPdpCartData();
			$designId = isset($pdpCartData['design_id']) ? $pdpCartData['design_id'] : 0;
			if($designId > 0)
			{
				  // assuming you are posting your custom form values in an array called extra_options...
			   $product = $observer->getProduct();
				// add to the additional options array
				$additionalOptions = array();
				if ($additionalOption = $product->getCustomOption('additional_options'))
				{
					$additionalOptions = (array) unserialize($additionalOption->getValue());
				}
				$additionalOptions[] = array(
					'code' => 'pdpinfo',
					'label' => 'Design Information',
					'value' => '',
					'json' => '',
					'time' => microtime()
				);
				if(isset($pdpCartData['pdp_print_type']['title']) && $pdpCartData['pdp_print_type']['title'] != '')
				{
					$additionalOptions[] = array(
						'label' => Mage::helper('integration')->__('Print Type'),
						'value' => $pdpCartData['pdp_print_type']['title'],
					);
				}
				if(isset($pdpCartData['product_color']['color_name']) && $pdpCartData['product_color']['color_name'] != '')
				{
					$additionalOptions[] = array(
						'label' => Mage::helper('integration')->__('Corol'),
						'value' => $pdpCartData['product_color']['color_name'],
					);
				}
				$pdpOptions = isset($pdpCartData['pdp_options']) ? $pdpCartData['pdp_options'] : array();
				$pdpAdditionalOptions = Mage::helper('integration')->getPdpAdditionalOption($pdpOptions);
				$pdpAdditionalOptions = $pdpAdditionalOptions['additional_options'];
				$additionalOptions = array_merge($additionalOptions,$pdpAdditionalOptions);
				// add the additional options array with the option code additional_options
				$observer->getProduct()
					->addCustomOption('additional_options', serialize($additionalOptions));
			}
          
        }
    }
	public function changePrice($observer) 
	{	
		$productPrice = $observer->getProduct()->getFinalPrice();
		$item = $observer->getQuoteItem();
		// Ensure we have the parent item, if it has one
		$item = ($item->getParentItem() ? $item->getParentItem() : $item);
		$buyRequest = $item->getBuyRequest()->getData();
		$pdpOption = isset($buyRequest['pdp_option']) ? $buyRequest['pdp_option'] : array();
		$designId = isset($pdpOption['design_id']) ? (int)$pdpOption['design_id'] : 0;
		if($designId > 0)
		{
			//pdp option
			$pdpOptions = isset($pdpOption['pdp_custom_option']) ? $pdpOption['pdp_custom_option'] : array();
			$pricePdp = isset($pdpOption ['price']) ? (float)$pdpOption['price'] : 0;
			$printPrice = 0;
			if(isset($pdpOption['print_type']['price']))
			{
				$printPrice = (float)$pdpOption['print_type']['price'];
			} 
			// print_r($pdpOptions);
			$pdpAdditionalOptions = Mage::helper('integration')->getPdpAdditionalOption($pdpOptions);
			$pdpOptionPrice = (float)$pdpAdditionalOptions['price'];
			$finalPrice = $pricePdp  + $pdpOptionPrice + $printPrice;
			
			$item->setCustomPrice($finalPrice);
			$item->setOriginalCustomPrice($finalPrice);
			// Enable super mode on the product.
			$item->getProduct()->setIsSuperMode(true);
		}
		
    }
	/**
	* after customer login
	**/
	function customerLogin($observer)
	{
		$customer = $observer->getCustomer();
		if($customer->getId() && $customer->getId() > 0)
		{
			$this->savePdpSessionDesings($customer->getId());
		}
	}
	/**
	* after customer login
	**/
	function customerRegisterSuccess()
	{
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			if($customer->getId() && $customer->getId() > 0)
			{
				$this->savePdpSessionDesings($customer->getId());
			}
		}
	}
	/**
	* save session custom design
	* @param $customId, array $designSession
	* @return PDP_Integration_Model_Customerdesigns
	**/
	private function savePdpSessionDesings($customId)
	{
		$customerDesigns = Mage::getSingleton('core/session')->getPdpCustomerDesigns();
		if(count($customerDesigns))
		{
			foreach($customerDesigns as $customerDesign)
			{
				$customerDesign['customer_id'] = $customId;
				Mage::getModel('integration/customerdesigns')->setData($customerDesign)->save();
			}
		}
		Mage::getSingleton('core/session')->setPdpCustomerDesigns(array());
	}
	public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getItem();
        if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
            $orderItem = $observer->getOrderItem();
            $options = $orderItem->getProductOptions();
            $options['additional_options'] = unserialize($additionalOptions->getValue());
            $orderItem->setProductOptions($options);
        }
    }
}