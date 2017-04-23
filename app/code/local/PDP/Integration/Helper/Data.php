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
	/**
	* get additional options
	* @param array $options
	* @return array
	**/
	function getPdpAdditionalOption($pdpOptions)
	{
		$additionalOptions = array();
		$price = 0;
		if(count($pdpOptions))
		{
			foreach($pdpOptions as $pdpOption)
			{
				if($pdpOption['type'] == 'field' || $pdpOption['type'] == 'area')
				{
					$price += $pdpOption['price'];
					$additionalOptions[] = array( 
						'label' => $pdpOption['title'],
						'value' => $pdpOption['default_text']
					);
				}
				elseif($pdpOption['type'] == 'drop_down' || $pdpOption['type'] == 'radio')
				{
					$title = $pdpOption['title'];
					if(isset($pdpOption['values']))
					{
						$pdpOptionValues = $pdpOption['values'];
						if(count($pdpOptionValues))
						{
							foreach($pdpOptionValues as $pdpOptionValue)
							{
								$value = $this->getPdpOptionValueTitle($pdpOptionValue['option_type_id']);
								if($value != '')
								{
									$price += $pdpOptionValue['price'];
									$additionalOptions[] = array( 
										'label' => $title,
										'value' => $value
									);
									break;
								}
							}
						}
					}
				}
				elseif($pdpOption['type'] == 'checkbox' || $pdpOption['type'] == 'multiple')
				{
					$title = $pdpOption['title'];
					if(isset($pdpOption['values']))
					{
						$pdpOptionValues = $pdpOption['values'];
						if(count($pdpOptionValues))
						{
							$value = '';
							foreach($pdpOptionValues as $pdpOptionValue)
							{
								$price += $pdpOptionValue['price'];
								if($value == '')
								{
									$value = $this->getPdpOptionValueTitle($pdpOptionValue['option_type_id']);
								}
								else
								{
									$value .= ', '.$this->getPdpOptionValueTitle($pdpOptionValue['option_type_id']);
								}
							}
							if($value != '')
								{
									$additionalOptions[] = array( 
										'label' => $title,
										'value' => $value
									);
								}
						}
					}
				}
			}
		}
		return array(
			'additional_options' => $additionalOptions,
			'price' => $price
		);
	}
	/**
	* get title option value 
	* @param int $optionId
	* return string
	**/
	private function getPdpOptionValueTitle($optionId)
	{
		$read = Mage::getSingleton('core/resource')->getConnection('core_read'); 
		$items = $read->fetchAll("SELECT title FROM pdp_product_option_type_title WHERE option_type_id = {$optionId}"); 
		$title = '';
		if(count($items))
		{
			foreach($items as $item)
			{
				$title = $item['title'];
				break;
			}
		}
		return $title;
	}
	/**
	* get pdp product
	* @param string $sku
	* @return array
	**/
	function getPdpProduct($sku)
	{
		$read = Mage::getSingleton('core/resource')->getConnection('core_read'); 
		$items = $read->fetchAll("SELECT type_id,sku FROM pdp_product_type WHERE sku = '{$sku}'");
		$pdpProduct = array();
		if(count($items))
		{
			foreach($items as $item)
			{
				$pdpProduct = $item;
			}
		}
		return $pdpProduct;
	}
	function checkPdpProduct($sku)
	{
		$pdpProduct = $this->getPdpProduct($sku);
		if(count($pdpProduct))
		{
			return true;
		}
		return false;
	}
}
?>