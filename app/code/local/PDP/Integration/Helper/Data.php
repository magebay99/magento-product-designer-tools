<?php
class PDP_Integration_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	* get PDP design order 
	* @param int $disignId
	* @return $item
	**/
	function getPdpDesignOrder($designId)
	{
		$read = Mage::getSingleton('core/resource')->getConnection('core_read'); 
		$items = $read->fetchAll("select side_thumb,svg_file from pdp_design_json where design_id = {$designId}"); 
		return $items[0] ? $items[0] : array();
	}
}
?>