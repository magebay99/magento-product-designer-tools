<?php


/**
 * PDP Data helper
 *
 * @author PDP-Developer Team <info@magebay.com>
 * @method array getPdpDesignOrder(int $designId)
 * @method array getPdpProduct(string $sku)
 * @method array getPdpSideData(int $sideId)
 * @method string getPdpOptionValueTitle(int $optionId)
 */
class PDP_Integration_Helper_Data extends Mage_Core_Helper_Abstract
{
	const USE_DESIGN_POPUP = 'integration/setting/use_popup_design';
	const SEPARATE_DATABASE = 'integration/setting/separate_database';
    /**
     * If true , then data will be fetched via CURL instead SQL querry (PDP & Magento not same db)
     * @var bool
     * @author Zuko
     * @since v1.2
     */
	private $_curlToPdpApi = true;

    /**
     * @var \PDP_Integration_Helper_Curl
     * @author Zuko
     * @since v1.2
     */
	private $_curl;

    /**
     * @return \PDP_Integration_Helper_Curl
     * @author Zuko
     * @since v1.2
     */
    public function getCurl()
    {
        if(!$this->_curl)
        {
            $this->_curl = new PDP_Integration_Helper_Curl();
        }
        return $this->_curl;
    }

    //<editor-fold desc="Get PDP Data Hybrid methods">
    /**
     * Call function by case
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \ErrorException
     * @throws \Exception
     * @author Zuko
     * @since v1.2
     */
    public function __call($name, $arguments)
    {
        $methods = ['getPdpDesignOrder','getPdpProduct','getPdpSideData','getPdpOptionValueTitle'];
        if(in_array($name, $methods))
        {
            try{
                $toCall = $name . 'ViaCurl';
                if(!$this->isSeparateDb())
                {
                    $toCall = $name . 'Sql';
                }
                return call_user_func_array(array($this, $toCall), $arguments);
            }catch (Exception $e)
            {
                throw $e;
            }
        }
        throw new ErrorException(sprintf('Fatal : method %s does not exits in %k',$name,get_class($this)));
    }


    /**
     * @param string $sku
     * @return bool
     * @throws \Exception
     * @author Zuko
     * @since v1.2
     */
    function checkPdpProduct($sku)
    {
        try{
            $pdpProduct = $this->getPdpProduct($sku);
            if(count($pdpProduct))
            {
                return true;
            }
            return false;
        }catch (Exception $e)
        {
            throw $e;
        }
    }



    /**
     * get additional options
     *
     * @param $pdpOptions
     * @return array
     */
    public function getPdpAdditionalOption($pdpOptions)
    {
        $additionalOptions = [];
        $price = 0;
        if (count($pdpOptions))
        {
            foreach ($pdpOptions as $pdpOption)
            {
                if ($pdpOption['type'] == 'field' || $pdpOption['type'] == 'area')
                {
                    $price += $pdpOption['price'];
                    $additionalOptions[] = [
                        'label' => $pdpOption['title'],
                        'value' => $pdpOption['default_text'],
                    ];
                }
                else if ($pdpOption['type'] == 'drop_down' || $pdpOption['type'] == 'radio')
                {
                    $title = $pdpOption['title'];
                    if (isset($pdpOption['values']))
                    {
                        $pdpOptionValues = $pdpOption['values'];
                        if (count($pdpOptionValues))
                        {
                            foreach ($pdpOptionValues as $pdpOptionValue)
                            {
                                /*if(!$this->_curlToPdpApi)
                                {
                                    $value = $this->getPdpOptionValueTitleSql($pdpOptionValue['option_type_id']);
                                }else{
                                    $value = $this->getPdpOptionValueTitleViaCurl($pdpOptionValue['option_type_id']);
                                }*/
                                $value = $this->getPdpOptionValueTitle($pdpOptionValue['option_type_id']);
                                if ($value != '')
                                {
                                    $price += $pdpOptionValue['price'];
                                    $additionalOptions[] = [
                                        'label' => $title,
                                        'value' => $value,
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                }
                else if ($pdpOption['type'] == 'checkbox' || $pdpOption['type'] == 'multiple')
                {
                    $title = $pdpOption['title'];
                    if (isset($pdpOption['values']))
                    {
                        $pdpOptionValues = $pdpOption['values'];
                        if (count($pdpOptionValues))
                        {
                            $value = '';
                            foreach ($pdpOptionValues as $pdpOptionValue)
                            {
                                $price += $pdpOptionValue['price'];
                                if ($value == '')
                                {
                                    /*if(!$this->_curlToPdpApi)
                                    {
                                        $value = $this->getPdpOptionValueTitleSql($pdpOptionValue['option_type_id']);
                                    }else{
                                        $value = $this->getPdpOptionValueTitleViaCurl($pdpOptionValue['option_type_id']);
                                    }*/
                                    $value = $this->getPdpOptionValueTitle($pdpOptionValue['option_type_id']);
                                }
                                else
                                {
                                    /*if(!$this->_curlToPdpApi)
                                    {
                                        $value = $this->getPdpOptionValueTitleSql($pdpOptionValue['option_type_id']);
                                    }else{
                                        $value = $this->getPdpOptionValueTitleViaCurl($pdpOptionValue['option_type_id']);
                                    }*/
                                    $value = $this->getPdpOptionValueTitle($pdpOptionValue['option_type_id']);
                                }
                            }
                            if ($value != '')
                            {
                                $additionalOptions[] = [
                                    'label' => $title,
                                    'value' => $value,
                                ];
                            }
                        }
                    }
                }
            }
        }

        return [
            'additional_options' => $additionalOptions,
            'price'              => $price,
        ];
    }


    /**
     * Set setCurlToPdpApi
     *
     * @param bool $curlToPdpApi
     * @return PDP_Integration_Helper_Data
     * @author Zuko
     * @since v1.2
     */
    public function setCurlToPdpApi($curlToPdpApi)
    {
        $this->_curlToPdpApi = $curlToPdpApi;

        return $this;
    }
    //</editor-fold>

	/**
	* 
	* Retrieve true if using popup for design
	* @return boolean
	*/
	public function isUseDesignPopup() {
		return Mage::getStoreConfig(self::USE_DESIGN_POPUP);
	}
        
        /**
	* 
	* Retrieve true if separate database
	* @return boolean
	*/
	public function isSeparateDb() {
		return Mage::getStoreConfig(self::SEPARATE_DATABASE);
	}


    /**
     * get pdp link
     *
     * @return string $link
     * @internal param $pdpSetting
     */
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
     *
     * @param $domain_name
     * @return string $url
     */
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

    //<editor-fold desc="Get Data via Curl methods" defaultstate="collapsed">
    /**
     * @param $result
     * @return bool
     * @author Zuko
     * @since v1.2
     */
    private function checkCurlJson($result)
    {
        if($result)
        {
            if(is_array($result) && $result['status'] == 'success')
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $sku
     * @return mixed
     * @throws \ErrorException
     * @author Zuko
     * @since v1.2
     */
    public function getPdpProductViaCurl($sku)
    {
        $url = rtrim($this->getPdpLink(),'/');
        $url .= '/rest/commerce?product=1&sku=' . $sku;
        /* init curl */
        $this->getCurl()->setUrl($url);
        $result = $this->getCurl()->exec();
        if($result && $this->checkCurlJson($result))
        {
            return array_shift($result['data']);
        }
        throw new ErrorException(sprintf('Failed to fetch PDP Product Data with SKU : %s',$sku));
    }

    /**
     * get PDP design order via Curl requesting
     *
     * @param $designId
     * @return array
     * @throws \ErrorException
     * @author Zuko
     * @since v1.2
     */
    public function getPdpDesignOrderViaCurl($designId)
    {
        $url = rtrim($this->getPdpLink(),'/');
        $url .= '/rest/design-template?id=' . $designId;
        /* init curl */
        $this->getCurl()->setUrl($url);
        $result = $this->getCurl()->exec();
        if($result && $this->checkCurlJson($result))
        {
            return $result['data'];
        }
        return array();
    }

    /**
     * get side data
     *
     * @param int $sideId
     * @return array $item
     * @throws \ErrorException
     * @author Zuko
     * @since v1.2
     */
    function getPdpSideDataViaCurl($sideId)
    {
        $url = rtrim($this->getPdpLink(),'/');
        $url .= '/rest/design-side?id=' . $sideId;
        /* init curl */
        $this->getCurl()->setUrl($url);
        $result = $this->getCurl()->exec();
        if($result && $this->checkCurlJson($result))
        {
            return $result['data'];
        }
        return array();
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * get title option value
     *
     * @param int $optionId
     * @return string
     * @author Zuko
     * @since v1.2
     */
    private function getPdpOptionValueTitleViaCurl($optionId)
    {
        $title = '';
        try
        {
            $url = rtrim($this->getPdpLink(),'/');
            $url .= '/rest/design-side?id='.$optionId;/* init curl */
            $this->getCurl()->setUrl($url);
            $result = $this->getCurl()->exec();
            if ($result && $this->checkCurlJson($result))
            {
                $option = $result['data'];
                if ($option['id'])
                {
                    return $option['title'];
                }
            }

            return $title;
        }
        catch (ErrorException $e)
        {
            return $title;
        }
    }
    //</editor-fold>
    //<editor-fold desc="SQL Query method" defaultstate="collapsed">
    /**
     * get PDP design order
     *
     * @param int $designId
     * @return array $item
     */
    public function getPdpDesignOrderSql($designId)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $items = $read->fetchAll("select side_thumb,svg_file from pdp_design_json where design_id = {$designId}");
        return $items[0] ? $items[0] : array();
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * get title option value
     *
     * @param int $optionId
     * @return string
     */
    private function getPdpOptionValueTitleSql($optionId)
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
    function getPdpProductSql($sku)
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

    /**
     * get side data
     *
     * @param int $sideId
     * @return array
     */
    public function getPdpSideDataSql($sideId)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $items = $read->fetchAll("select side_name from pdp_design_side where side_id = {$sideId}");
        return $items[0] ? $items[0] : array();
    }
    //</editor-fold>
}
