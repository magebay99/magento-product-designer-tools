<?php
class PDP_Integration_Block_Customerdesign extends Mage_Core_Block_Template
{
	public function __construct()
    {
        parent::__construct();
        $collection = Mage::getModel('integration/customerdesigns')->getCollection();
        //Filter by customer ID
        $customer = Mage::getSingleton("customer/session")->getCustomer();
        $collection->addFieldToFilter("customer_id", $customer->getId());
		$collection->setOrder("integration_id", "desc");
        $this->setCollection($collection);
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
 
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'All'));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
	function getMageProductById($productId)
	{
		return Mage::getModel('catalog/product')->load($productId);
	}
	/**
	* get all design completed
	* @param int $customId
	* @return array
	**/
	function getAllCompleteDesigns()
	{
		$designIds = array();
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			if($customer->getId() && $customer->getId() > 0)
			{
				$orderCollection = Mage::getResourceModel('sales/order_collection')
				->addFieldToSelect('*')
				->addFieldToFilter('customer_id', $customer->getId())
				->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
				->setOrder('created_at', 'desc');
				foreach($orderCollection as $order)
				{
					$items = $order->getAllItems();
					
					if($order->getStatus() == 'complete')
					{
						foreach($items as $item)
						{
							$request = $item->getBuyRequest()->getData();
							$pdpOptions = isset($request['pdp_option']) ? $request['pdp_option'] : array();
							if(count($pdpOptions))
							{
								$designId = isset($pdpOptions['design_id']) ? (int)$pdpOptions['design_id'] : 0;
								if($designId > 0)
								{
									$designIds[$designId] = $item->getRowTotal();
								}
							}
						}
					}
					
				}
			}
		}
		return $designIds;
	}
}