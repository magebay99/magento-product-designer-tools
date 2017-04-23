<?php
class PDP_Integration_Block_Pdpproduct extends Mage_Core_Block_Template
{
	public function getCurrentProduct() {
        $currentProduct =  Mage::registry('current_product');
        if ($currentProduct != NULL) {
            return $currentProduct;
		}
        return null;
    }
}