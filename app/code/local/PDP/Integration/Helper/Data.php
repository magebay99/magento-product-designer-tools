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
	/**
	* get pdp link
	* @param $pdpSetting
	* @return string $link
	**/
	function getPdpLink()
	{
		$pdpConfig = Mage::getStoreConfig('integration');
		$pdpUrl = $pdpConfig['setting']['pdp_link'];
		if($this->validate_domain_name($pdpUrl))
		{
			
		}
		else
		{
			$pdpUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK).$pdpUrl.'/';
			$pdpUrl = str_replace('index.php/','',$pdpUrl);
			$pdpUrl = $this->getPdpUrl($pdpUrl);
		}
		return $pdpUrl;
	}
	/**
	 * checks if a domain name is valid
	 * @param  string $domain_name 
	 * @return bool              
	 */
	public function validate_domain_name($domain_name)
	{
		//FILTER_VALIDATE_URL checks length but..why not? so we dont move forward with more expensive operations
		$domain_len = strlen($domain_name);
		if ($domain_len < 3 OR $domain_len > 253)
			return FALSE;

		//getting rid of HTTP/S just in case was passed.
		if(stripos($domain_name, 'http://') === 0)
			$domain_name = substr($domain_name, 7); 
		elseif(stripos($domain_name, 'https://') === 0)
			$domain_name = substr($domain_name, 8);

		//we dont need the www either                 
		if(stripos($domain_name, 'www.') === 0)
			$domain_name = substr($domain_name, 4); 

		//Checking for a '.' at least, not in the beginning nor end, since http://.abcd. is reported valid
		if(strpos($domain_name, '.') === FALSE OR $domain_name[strlen($domain_name)-1]=='.' OR $domain_name[0]=='.')
			return FALSE;
		//now we use the FILTER_VALIDATE_URL, concatenating http so we can use it, and return BOOL
		return (filter_var ('http://' . $domain_name, FILTER_VALIDATE_URL)===FALSE)? FALSE:TRUE;

	}
	/**
	* get domain name from url 
	* @param string $url
	* @return string $url
	**/
	private function getPdpUrl($domain_name)
	{
		$url = '';
		$http = 'http://';
		$okDomain = true;
		$domain_len = strlen($domain_name);
		if ($domain_len < 3 OR $domain_len > 253)
		{
			$okDomain = false;
		}
		//getting rid of HTTP/S just in case was passed.
		if(stripos($domain_name, 'http://') === 0)
			$domain_name = substr($domain_name, 7); 
		elseif(stripos($domain_name, 'https://') === 0)
		{
			$domain_name = substr($domain_name, 8);
			$http = 'https://';
		}
		//we dont need the www either                 
		if(stripos($domain_name, 'www.') === 0)
			$domain_name = substr($domain_name, 4); 

		//Checking for a '.' at least, not in the beginning nor end, since http://.abcd. is reported valid
		if(strpos($domain_name, '.') === FALSE OR $domain_name[strlen($domain_name)-1]=='.' OR $domain_name[0]=='.')
		{
			$okDomain = false;
		}
		if($okDomain)
		{
			$url  = $http.$domain_name;
		}
		return $url;
	}
}
?>