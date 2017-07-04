<?php
Class PDP_Integration_CustomerdesignController extends Mage_Core_Controller_Front_Action
{
	public function indexAction() {
        parent::preDispatch();
       	if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
	       // adding message in customer login page 
	       Mage::getSingleton('core/session')
                ->addError(Mage::helper('integration')->__('Please sign in or create a new account!'));
        }
		$this->loadLayout();
		$this->renderLayout();
	}
	function setDpkAction()
	{
		
		$designId = $this->getRequest()->getParam('design_id',0);
		Mage::getSingleton('core/session')->setPdpRedirect($designId);
		$response = array('status'=>'success');
		$this->getResponse()->setBody(json_encode($response));
	}
}