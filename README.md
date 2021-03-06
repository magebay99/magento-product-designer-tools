Demo Online Product Designer Pro https://goo.gl/kYho1j 
# About Online Products Designer Tools #PDP
Products Designer Pro (#PDP) are compatible with the most popular ecommerce platforms, such as Magento 1.9.x, Magento 2.x, Woocommerce, OpenCart, Shopify, Prestashop. It provides solution for online printing on demand services.
![Alt text](https://productsdesignerpro.com/wp-content/uploads/2016/08/mockup-on-ipad.jpg "Push multiple products") 
Helpful links:
- How it works? Check here https://goo.gl/2WE8Or
- Key Features  https://goo.gl/Plj3qo
- Pricing https://goo.gl/vq7LIr
- Help Center https://goo.gl/fWjkKz

# Free download The Magento 1.9.x Connector plugin for the Product Designer Tools 
- The Magento 1.9.x Connector plugin will connect Magento 1.9.x with Product Designer Pro system https://goo.gl/AFlnBQ
- Manage order and export all customized designs from Magento Backend (Export and Edit customized design from customer's order details)
- Manage personalize products which was pushed by #PDP (Update information)
- Customer can save customized design in My Customized Design section (Magento user can save or order customized design)
- Source code is open for any suggest or customization the shopping cart rules.
- Only contributors can submit commit.
# Download
[Lastest Version](https://github.com/magebay99/magento-product-designer-tools/archive/master.zip)
# Installation Guide 

1. Disable Cache of Magento 1.9.x from System Cache management.
2. Download and upload into your Magento 1.9.x root directory
3. Refresh cache and logout to ignore authentication 404 issues
4. Config module in Magento Backend

Disable Cache

![Alt text](https://productsdesignerpro.com/images/disable-cache-m1.png "Disable Cache") 

Configuration the connector module

![Alt text](https://productsdesignerpro.com/images/pdp-config-magento1.png "Configuration the connector module")

Enter installed path of PDP. For example domain.com/designer then just enter 'designer'

![Alt text](https://productsdesignerpro.com/images/pdp-config-magento1-path.png "Enter installed path of PDP. For example domain.com/designer then just enter 'designer'")

# One click to push products from PDP to Magento1.9.x

1. Config Connector #PDP with Magento 1.9.x.  
- Enable Shopping cart and connect to Magento 1.9.x:

![Alt text](http://image.prntscr.com/image/d590b720a652453da0851ae3d8770309.png "Enable Shopping Cart") 

- Enter API access information to connect with Magento 1.9.x(it can be Magento admin access)
![Alt text](https://productsdesignerpro.com/images/config-magento1-with-pdp.png "Disable Cache") 
Check this post for tutor how to get Consumer key and secret https://goo.gl/z9ZjtA 

2. Push products into Magento 1.9.x for ready to sell.
- Push single product to Magento 1.9.x
![Alt text](http://image.prntscr.com/image/c9a9e469a1a046b5a8efcb5fc7d849be.png "Push single product to live") 
- Push multiple products 
![Alt text](http://g.recordit.co/wPC1LI8pcw.gif "Push multiple products (max 12)") 

After products are pushed succesful, all these items status will be change to LIVE in #PDP, and ready for sell on Magento 1.9.x.

We have not tested on Magento 1.8.x or less than version number.
3 . Display Button Customize it in category page. 
![2017-04-24_1417 1](https://cloud.githubusercontent.com/assets/11420761/25326607/286007e0-28fb-11e7-8b92-387d0bb06261.png)

Open file app/design/frontend/YOUR_PACKAGE/YOUR_THEME/template/catalog/product/list.phtml

find code 

`<button type="button" title="<?php echo $this->quoteEscape($this->__('Add to Cart')) ?>" class="button btn-cart" onclick="setLocation('<?php echo $this->getAddToCartUrl($_product) ?>')"><span><span><?php echo $this->__('Add to Cart') ?></span></span></button>`

Change it to 

`<button <?php if($ispdp) { echo 'style="display: none"'; } ?> type="button" title="<?php echo $this->quoteEscape($this->__('Add to Cart')) ?>" class="button btn-cart" onclick="setLocation('<?php echo $this->getAddToCartUrl($_product) ?>')"><span><span><?php echo $this->__('Add to Cart') ?></span></span></button>`

Add the code to above it


` <?php $ispdp = false; ?>
							<?php
								if(Mage::helper('core')->isModuleEnabled('PDP_Integration'))
								{
									if(Mage::helper('integration')->checkPdpProduct($_product->getSku()))
									{
										$ispdp = true;
									}
								}
							?>`
           
 
Add the code to below it 

`<?php echo Mage::app()->getLayout()->createBlock('integration/pdpproduct')->setData(array('product_data'=>$_product))->setTemplate('integration/product/list.phtml')->toHtml(); ?>
`
![2017-04-24_1454 1](https://cloud.githubusercontent.com/assets/11420761/25327378/1d2024c0-28fe-11e7-889e-ad53d7e411ff.png)

See Customized Design in Admin Order

Go To Sales => Order 
![ymlhsnx](https://user-images.githubusercontent.com/11420761/27023912-c726a218-4f7e-11e7-8485-15dc08912163.png)

See Customized Design in Customer Email . If Store Send an email to user's email ater completing order. He can see Link design in his email. He can click to link view detail design. 
![firefox_2017-06-12_14-49-26](https://user-images.githubusercontent.com/11420761/27023834-73ec6330-4f7e-11e7-9250-6d14afc55f62.png)

See Customized Design in Customer Account Page. If user access to his account page. He can see the design in his order.
![6ghcn1o](https://user-images.githubusercontent.com/11420761/27023881-a02827c2-4f7e-11e7-93e7-7a07af91f962.png)



# Module Connector for other e-commerce platform

- Magento 2.X Connector https://goo.gl/zxpHbJ
- Magento 1.X Connector https://goo.gl/lg3v4g
- Woocommerce Connector https://goo.gl/3bQ1Br
- Shopify Connector https://goo.gl/1OmgM3
- Prestashop Connector https://goo.gl/SHG4RR
- Opencart Connector https://goo.gl/GEJ3Nk
- Nopcommerce Connector https://goo.gl/GEJ3Nk
