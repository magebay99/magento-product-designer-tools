<?php
class PDP_Integration_Block_Additional_Order_Info extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $item = $this->getParentBlock()->getItem();
        $block = $this->getLayout()->createBlock('core/template')
                ->setTemplate('integration/order/info.phtml')
                ->setData('item', $item);
        return $block->toHtml();
    }
}