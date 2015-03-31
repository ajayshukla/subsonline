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

class Kontilint_News_Block_Item extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
    	
    	$item = $this->getItem();
    	
    	if ( Mage::getStoreConfig('web/default/show_cms_breadcrumbs') && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) ) {
    		$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('page')->__('Home'), 'title'=>Mage::helper('page')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
			$breadcrumbs->addCrumb('news_home', array('label' => Mage::helper('knews')->getListPageTitle(), 'title' => Mage::helper('knews')->getListPageTitle(), 'link' => Mage::helper('knews')->getUrl()));
			$breadcrumbs->addCrumb('news', array('label' => $item->getTitle(), 'title' => $item->getTitle()));
    	}
        
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle(Mage::helper('knews')->getDetailTitlePrefix() . $item->getTitle());
            $head->setKeywords($item->getMetaKeywords() ? $item->getMetaKeywords() : Mage::helper('knews')->getDetailDefaultMetaKeywords());
            $head->setDescription($item->getMetaDescription() ? $item->getMetaDescription() : Mage::helper('knews')->getDetailDefaultMetaDescription());
        }

        return parent::_prepareLayout();
        
    }
    
     public function getItem()     
     { 
        if (!$this->hasData('item')) {
            $this->setData('item', Mage::registry('item'));
        }
        return $this->getData('item');
        
    }
}
