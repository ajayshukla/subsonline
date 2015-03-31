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

class Kontilint_News_Helper_Data extends Mage_Core_Helper_Abstract
{


	const XML_PATH_ENABLED						=	'knews/general/enabled';
	const XML_PATH_ENABLED_RSS					=	'knews/general/enabled_rss';
	const XML_PATH_LIST_PAGE_TITLE				=	'knews/list/page_title';
	const XML_PATH_LIST_IDENTIFIER				=	'knews/list/identifier';
	const XML_PATH_LIST_ITEMS_PER_PAGE			=	'knews/list/items_per_page';
	const XML_PATH_LIST_LIMIT_DESCRIPTION		=	'knews/list/limit_description';
	const XML_PATH_LIST_META_DESCRIPTION		=	'knews/list/meta_description';
	const XML_PATH_LIST_META_KEYWORDS			=	'knews/list/meta_keywords';
	const XML_PATH_DETAIL_TITLE_PREFIX			=	'knews/detail/title_prefix';
	const XML_PATH_DETAIL_DEFAULT_META_DESCRIPTION	=	'knews/detail/default_meta_description';
	const XML_PATH_DETAIL_DEFAULT_META_KEYWORDS	=	'knews/detail/default_meta_keywords';
	const XML_PATH_SEO_URL_SUFFIX				=	'knews/seo/url_suffix';
	
	
    public function isAllow()
    {
    	return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }
    
    public function isAllowRss()
    {
    	return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED_RSS);    	
    }
	
	public function getListPageTitle()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_PAGE_TITLE);
	}
	
	public function getListIdentifier()
	{
		$identifier = Mage::getStoreConfig(self::XML_PATH_LIST_IDENTIFIER);
		if ( !$identifier ) {
			$identifier = 'news';
		}
		return $identifier;
	}
	
	public function getUrl($identifier = null)
	{
		if ( is_null($identifier) ) {
			$url = Mage::getUrl('') . self::getListIdentifier() . self::getSeoUrlSuffix();
		} else {
			$url = Mage::getUrl(self::getListIdentifier()) . $identifier . self::getSeoUrlSuffix();
		}

		return $url;
		
	}
	
	public function getListItemsPerPage()
	{
		return (int)Mage::getStoreConfig(self::XML_PATH_LIST_ITEMS_PER_PAGE);
	}
	
	public function getListLimitDescription()
	{
		return (int)Mage::getStoreConfig(self::XML_PATH_LIST_LIMIT_DESCRIPTION);
	}
	
	public function getListMetaDescription()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_META_DESCRIPTION);
	}
	
	public function getListMetaKeywords()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_META_KEYWORDS);
	}
	
	public function getDetailTitlePrefix()
	{
		return Mage::getStoreConfig(self::XML_PATH_DETAIL_TITLE_PREFIX);
	}
	
	public function getDetailDefaultMetaDescription()
	{
		return Mage::getStoreConfig(self::XML_PATH_DETAIL_DEFAULT_META_DESCRIPTION);
	}
	
	public function getDetailDefaultMetaKeywords()
	{
		return Mage::getStoreConfig(self::XML_PATH_DETAIL_DEFAULT_META_KEYWORDS);
	}
	
	public function getSeoUrlSuffix()
	{
		return Mage::getStoreConfig(self::XML_PATH_SEO_URL_SUFFIX);
	}
	public function getImage($image, $width = 100, $height = 100)
	{
		if ($image != '') {
			if (! file_exists(Mage::getBaseDir('media') . DS . 'resized')) {
				mkdir(Mage::getBaseDir('media') . DS . 'resized', 0777);
			}
			$imgobj = new Varien_image(Mage::getBaseDir('media') . DS . $image);
			$imgobj->constrainOnly(TRUE);
			$imgobj->keepaspectRatio(TRUE);
			$imgobj->resize($width, $height);
			$imgobj->save(Mage::getBaseDir('media') . DS . 'resized' . DS . 'thumb_' . $image);
			return '<img src="/media/resized/thumb_' . $image . '" border="0" />';
		}
	}
}

