<?php
Class PDP_Integration_CartController extends Mage_Core_Controller_Front_Action
{
	function prepareAction()
	{
		//set session for customer design
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
		 if(!isset($postData['pdpDesignItem'])) {
			//Try get data another way, fix mod_security problem
			$postString = file_get_contents("php://input");
			if($postString != "") {
                $_jsonInfo = json_decode($postString, true);
				$pdpData = $_jsonInfo['pdpDesignItem'];
				
			} else {

			}
        } else {
			$pdpData = $postData['pdpDesignItem'];
		}
		//find price and product id;
		if(isset($pdpData['product_sku']) && $pdpData['product_sku'] != '' && isset($pdpData['design_id']) && (int)$pdpData['design_id'] > 0) 
		{
			$designId = $pdpData['design_id'];
			$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $pdpData['product_sku']);
			$productId = ($_product && $_product->getId()) ? $_product->getId() : 0;
			if($productId > 0)
			{
				$dataDesign = Mage::helper('integration')->getPdpDesignOrder($designId);
				$strImages = $dataDesign['side_thumb_raw'] ? $dataDesign['side_thumb_raw']:$dataDesign['side_thumb'];
				$strThumbanail = '';
				if($strImages != '')
				{
					$images = unserialize($strImages);
					if(count($images))
					{
						foreach($images as $image)
						{
							if($image['thumb'] != '')
							{ 
								$strThumbanail = $image['thumb'];
								break;
							}
						}
					}
				}
				$finaPrice = $_product->getFinalPrice();
				$customerDesign = array(
					'design_id' => $designId,
					'mage_product_id' => $productId,
					'mage_product_sku'=>$pdpData['product_sku'],
					'thumbmail'=>$strThumbanail,
					'pdp_product_id'=>$pdpData['product_id'],
					'price' => $finaPrice
				);
				if(Mage::getSingleton('customer/session')->isLoggedIn()) {
					$customer = Mage::getSingleton('customer/session')->getCustomer();
					if($customer->getId() && $customer->getId() > 0)
					{
						$customerDesign['customer_id'] = $customer->getId();
						Mage::getModel('integration/customerdesigns')->setData($customerDesign)->save();
					}
				}
				else
				{
					$customerDesigns = Mage::getSingleton('core/session')->getPdpCustomerDesigns();
					$customerDesigns[] = $customerDesign;
					Mage::getSingleton('core/session')->setPdpCustomerDesigns($customerDesigns);
				}
				$response = array('status'=>'success','message'=>'You have added design to list');
			}
		}
		$this->getResponse()->setBody(json_encode($response));
	}
    function addAction()
    {
		$response = array('status'=>'error','message'=>'can not add product to cart','url'=>Mage::getBaseUrl().'checkout/cart');
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
		Mage::getSingleton('core/session')->setPdpCartData($pdpData);
		if(isset($pdpData['sku']) && $pdpData['sku'] != '') 
		{
			//
			$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $pdpData['sku']);
			$productId = ($_product && $_product->getId()) ? $_product->getId() : 0;
			$designId = $pdpData['design_id'];
			//$prinType = $pdpData['pdp_print_type'];
			if(isset($pdpData['pdp_print_type'])) {
				$prinType = $pdpData['pdp_print_type'];	
			}
			if(isset($pdpData['product_color'])) {
				$productColor = $pdpData['product_color'];	
			}			
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
							//'print_type'=>$prinType,
							//'product_color'=>$productColor,
							'price'=>$pdpPrice,
							'pdp_product_id'=>$pdpData['entity_id'],
							//'pdp_custom_option'=>$pdpData['pdp_options'],
							
						)
					);
					if(isset($productColor)) {
						$params['pdp_option']['product_color'] = $productColor;
					}
					if(isset($prinType)) {
						$params['pdp_option']['print_type'] = $prinType;
					}
					if(isset($pdpData['pdp_options'])) {
						$params['pdp_option']['pdp_custom_option'] = $pdpData['pdp_options'];
					}					
					$cart = Mage::getSingleton('checkout/cart');
					//if exit item, remove it
					$itemId = isset($pdpData['item_id']) ? $pdpData['item_id'] : 0;
					$itemId = (int)$itemId;
					if($itemId > 0)
					{
						$cart->removeItem($itemId);
					}
					$product = new Mage_Catalog_Model_Product();
					$product->load($productId);
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
				$this->getResponse()->setBody(json_encode($response));
			}
		}
    }
	function testAction()
	{
		/* $html=" put your html content here blah blah";
			$mail = Mage::getModel('core/email');
			$mail->setToName('admin@maiuoc.com');
			$mail->setToEmail('tuannguyensctn@gmail.com');
			$mail->setBody('Mail Text / Mail Content');
			$mail->setSubject('Mail Subject');
			$mail->setFromEmail('Sender Mail Id');
			$mail->setFromName("Msg to Show on Subject");
			$mail->setType('html');// YOu can use Html or text as Mail format

			try {
			$mail->send();
			Mage::getSingleton('core/session')->addSuccess('Your request has been sent');
			$this->_redirect('');
			}
			catch (Exception $e) {
			Mage::getSingleton('core/session')->addError('Unable to send.');
			$this->_redirect('');
			} */
	}
}