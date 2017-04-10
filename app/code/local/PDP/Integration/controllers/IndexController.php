<?php
Class PDP_Integration_IndexController extends Mage_Core_Controller_Front_Action
{
	function prepareAction()
	{
		$request = $this->getRequest()->getParams();
		
	}
    function indexAction()
    {
		$read = Mage::getSingleton('core/resource')->getConnection('core_read'); 
		/* $results = $read->fetchAll("select * from pdp_design_json where email like '%@codexpedia.com'"); 
		var_dump($results[0]);
		foreach($results as $r)
		{
			var_dump($r);
		} */
		$response = array('status'=>'error','message'=>'can not add product to cart');
		$session = Mage::getSingleton('customer/session'); 
		$productId = 1;
		// Get cart instance
		try{
        $params = array(
            'product' => $productId,
            'qty' => 1,
        );
        $cart = Mage::getSingleton('checkout/cart');

        $product = new Mage_Catalog_Model_Product();
        $product->load($productId);

		$additionalOptions = array(
			'label' => 'Total Days',
			'value' => 'total_days',
		);
		$product->addCustomOption('additional_options', serialize($additionalOptions));
        $cart->addProduct($product, $params);
        $cart->save();
		$response['message'] = 'you add product to cart success';
		$response['status'] = 'error';
		$this->getResponse()->setBody(json_encode($response));
		} catch(Exception $e) {
			$response['message'] = $e->getMessage();
			$this->getResponse()->setBody(json_encode($response));
		}
    }
	function testAction()
	{
		echo Mage::helper('integration')->__('Vai that');
	}
}