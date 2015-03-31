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
 * @copyright  Copyright (c) 2009 Kontilint Agency SA
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Kontilint
 * @package    Kontilint_News
 * @author     Mattias Bomelin <mattias@kontilint.se>
 */

class Kontilint_News_IndexController extends Mage_Core_Controller_Front_Action
{
	
	public function preDispatch()
    {
    
        parent::preDispatch();

        if( !Mage::helper('knews')->isAllow() ) {
            $this->norouteAction();
        }
        
    }
	
    public function indexAction()
    {       
    	$catId = $this->_request->getParam('category_id', null);
    	    
    	$coll = Mage::getModel('knews/news')->getCollection()
							->addStoreFilter(Mage::app()->getStore(true)->getId())
							->addFieldToFilter('main_table.is_active', 1);
	if ($catId != null && $catId > 0) $coll->addFieldToFilter('main_table.category', $catId);
	$coll->addOrder('main_table.date', 'desc');
	$collection = $coll->getData();

		$itemsPerPage = Mage::helper('knews')->getListItemsPerPage();
		
		// Use paginator
		if ( $itemsPerPage != 0 ) {		
			$paginator = Zend_Paginator::factory((array)$collection);
			$paginator->setCurrentPageNumber((int)$this->_request->getParam('page', 1))
					  ->setItemCountPerPage($itemsPerPage);
			Mage::register('items', $paginator);
		} else {
			Mage::register('items', $collection);
		}
				   	
    	$this->loadLayout();   
		$this->renderLayout();   
				
    }
    
    public function viewAction()
    {
    	
    	$itemId = $this->_request->getParam('id', null);
    	
    	if ( is_numeric($itemId) ) {
    	
      		$item = Mage::getModel('knews/news')
    						->setStoreId(Mage::app()->getStore()->getId())
    						->load($itemId);

			if ( !is_null($item->getIsActive()) ) {
			
				Mage::register('item', $item);
				$this->loadLayout();
				$this->renderLayout();
				return;

			}
			
    	}
    	
    	$this->_redirect('knews');
    					
    }
    
    public function rssAction()
    {
	
        if( !Mage::helper('knews')->isAllowRss() ) {
            $this->norouteAction();
        }
		
    	$storeId = $this->_request->getParam('id', null);
    	
    	if ( is_numeric($storeId) ) {
		
			$collection = Mage::getModel('knews/news')->getCollection()
								->addStoreFilter($storeId)
								->addFieldToFilter('main_table.is_active', 1)
								->addOrder('main_table.date', 'desc');
    	
			$feedArray = array (
				'title'			=>	'News',
				'link'			=>	Mage::getUrl('*/*/rss'),
				//'description'	=>	'',
				'language'		=>	'en-us',
				'charset'		=>	'utf-8',
				'pubDate'		=>	time(),
				//'generator'		=>	'',
				'entries'		=>	array()
			);
			
			foreach ( $collection->getData() as $_item ) {
						
				$link = Mage::helper('knews')->getUrl($_item['identifier']) .
								'?___website=' . Mage::app()->getStore($storeId)->getWebsite()->getCode() .
								'&amp;___store=' . Mage::app()->getStore($storeId)->getCode();
				
				$date = new Zend_Date($_item['date']);
				
				$feedArray['entries'][] = array (
					'title'			=>	$_item['title'],
					'link'			=>	$link,
					'guid'			=>	$link,
					'description'	=>	$_item['description'],
					'lastUpdate'	=>	$date->get(Zend_Date::TIMESTAMP)
				);
				
			}

			$feed = Zend_Feed::importArray($feedArray, 'rss');
			$feed->send();
			exit;
								
		}
    	
    }
    
    
}
