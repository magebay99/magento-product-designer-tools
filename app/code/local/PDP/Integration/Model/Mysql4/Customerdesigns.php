<?php
/**
 * @Author      : Magebay Team
 * @package     PDP Connect
 * @copyright   Copyright (c) 2014 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
Class PDP_Integration_Model_Mysql4_Customerdesigns extends Mage_Core_Model_Mysql4_Abstract
{
    function _construct()
    {
        $this->_init('integration/customerdesigns','integration_id');
    }
}