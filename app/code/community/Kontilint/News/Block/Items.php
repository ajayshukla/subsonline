<?php
/**
 * Kontilint_News extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Kontilint
 * @package    Kontilint_News
 * @copyright  Copyright (c) 2010 Kontilint Data AB
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Kontilint
 * @package    Kontilint_News
 * @author     Mattias Bomelin <mattias@kontilint.se>
 */

class Kontilint_News_Block_Items extends Mage_Core_Block_Template
{
    
    public function _prepareLayout()
    {
        if ( Mage::getStoreConfig('web/default/show_cms_breadcrumbs') && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) ) {
            $breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
            $breadcrumbs->addCrumb('news_home', array('label' => Mage::helper('knews')->getListPageTitle(), 'title' => Mage::helper('knews')->getListPageTitle()));
        }
        
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle(Mage::helper('knews')->getListPageTitle());
            $head->setDescription(Mage::helper('knews')->getListMetaDescription());
            $head->setKeywords(Mage::helper('knews')->getListMetaKeywords());
        }
        
        return parent::_prepareLayout();
        
    }
    
	public function getItems()     
	{ 

		if ( !$this->hasData('items') ) {
			$this->setData('items', Mage::registry('items'));
		}
		return $this->getData('items');
        
	}
	
	public function getLimitDescription()
	{
		
		if ( !$this->hasData('limit_description') ) {
			$this->setData('limit_description', Mage::registry('limit_description'));
		}
		return $this->getData('limit_description');
		
	}
    
}
