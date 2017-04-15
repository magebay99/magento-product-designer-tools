<?php
Class PDP_Integration_CartController extends Mage_Core_Controller_Front_Action
{
    function addAction()
    {
		$response = array('status'=>'error','message'=>'can not add product to cart');
		$pdpConfig = Mage::getStoreConfig('integration');
		if(!isset($pdpConfig['setting']['enable']) || (isset($pdpConfig['setting']['enable']) && $pdpConfig['setting']['enable'] != 1))
		{
			$response = array('status'=>'error','message'=>'Module Connect is disabled');
			$this->getResponse()->setBody(json_encode($response));
			return;
		}
		$pdpData  = array();
		$postData = $this->getRequest()->getPost();
		 if(!isset($postData['pdpItem'])) {
			//Try get data another way, fix mod_security problem
			$postString = file_get_contents("php://input");
			if($postString != "") {
                $_jsonInfo = json_decode($postString, true);
				$pdpData = $_jsonInfo['pdpItem'];
				
			} else {

			}
        } else {
			$pdpData = $postData['pdpItem'];
		}
		if(isset($pdpData['sku']) && $pdpData['sku'] != '') 
		{
			//
			$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $pdpData['sku']);
			$productId = ($_product && $_product->getId()) ? $_product->getId() : 0;
			$designId = $pdpData['design_id'];
			$prinType = $pdpData['pdp_print_type'];
			$productColor = $pdpData['product_color'];
			$qty = $pdpData['qty'];
			$pdpPrice = $pdpData['price'];
			if($productId > 0)
			{
				// Get cart instance
				try{
					$params = array(
						'product' => $productId,
						'qty' => $qty ,
						'pdp_option'=>array(
							'design_id'=> $designId,
							'print_type'=>$prinType,
							'product_color'=>$productColor,
							'price'=>$pdpPrice,
							'pdp_product_id'=>$pdpData['entity_id'],
							
						)
					);
					$cart = Mage::getSingleton('checkout/cart');
					//if exit item, remove it
					$itemId = $this->getRequest()->getParam('itemid',0);
					$itemId = (int)$itemId;
					if($itemId > 0)
					{
						$cart->removeItem($itemId);
					}
					$product = new Mage_Catalog_Model_Product();
					$product->load($productId);
					$additionalOptions = array(
						'label' => 'Total Days',
						'value' => 'total_days',
					);
					//$product->addCustomOption('additional_options', serialize($additionalOptions));
					$cart->addProduct($product, $params);
					$cart->save();
					$response['message'] = 'you add product to cart success';
					$response['status'] = 'success';
					$this->getResponse()->setBody(json_encode($response));
				} catch(Exception $e) {
					$response['message'] = $e->getMessage();
					$this->getResponse()->setBody(json_encode($response));
				}
			}
			else
			{
				$response['message'] = $this->__('Product does not exit');
			}
		}
    }
	function testAction()
	{
		$read = Mage::getSingleton('core/resource')->getConnection('core_read'); 
		$results = $read->fetchAll("select * from pdp_design_json where design_id = 8"); 
		$additionalOptions = array(
                    'code' => 'pdpinfo_lable',
                    'label' => 'Customized Design:',
                    'value' => '',
                    'json' => '',
                	'time' => microtime()
                );
		foreach($results as $r)
		{
			echo '<pre>';
				print_r($r);
			echo '</pre>';
		}
	}
}