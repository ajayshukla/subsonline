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

class Kontilint_News_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function initControllerRouters($observer)
    {    
        $front = $observer->getEvent()->getFront();

        $news = new Kontilint_News_Controller_Router();
        $front->addRouter('news', $news);
        
    }

    public function match(Zend_Controller_Request_Http $request)
    {
 
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
		
		if ( !Mage::helper('knews')->isAllow() ) {
			return false;
		}

        $route = Mage::helper('knews')->getListIdentifier();
        $identifier = trim($request->getPathInfo(), '/');
        $identifier = str_replace(Mage::helper('knews')->getSeoUrlSuffix(), '', $identifier);
                
        if ( $identifier == $route ) {
        
        	$request->setModuleName('news')
        			->setControllerName('index')
        			->setActionName('index');
        			
        	return true;
        			
        } elseif ( strpos($identifier, $route . '/rss') === 0 && !is_null($request->getParam('id', null)) && Mage::helper('knews')->isAllowRss() ) {
        	
        	$request->setModuleName('news')
        			->setControllerName('index')
        			->setActionName('rss')
        			->setParam('id', (int)$request->getParam('id'));
        			
        	return true;
        			        
        } elseif ( strpos($identifier, $route) === 0 && strlen($identifier) > strlen($route) ) {
        
        	$identifier = trim(substr($identifier, strlen($route)), '/');

		$catId = Mage::getModel('knews/news')->checkCategoryIdentifier($identifier, Mage::app()->getStore()->getId());
		if ($catId > 0) {
        		$request->setModuleName('news')
            		->setControllerName('index')
            		->setActionName('index')
            		->setParam('category_id', $catId);
            		
			$request->setAlias(
					Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
					$identifier
			);
			
			return true;
		}

	       	$newsId = Mage::getModel('knews/news')->checkIdentifier($identifier, Mage::app()->getStore()->getId());
        	if ( !$newsId ) {
            	return false;
        	}

        	$request->setModuleName('news')
            		->setControllerName('index')
            		->setActionName('view')
            		->setParam('id', $newsId);
            		
			$request->setAlias(
					Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
					$identifier
			);
			
			return true;

        }  
      echo "FALSE";exit(0); 
        return false;

    }
}
