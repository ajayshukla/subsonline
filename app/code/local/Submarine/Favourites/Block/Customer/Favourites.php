<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Submarine_Favourites
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Favourites block customer items
 *
 * @category   Mage
 * @package    Submarine_Favourites
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Submarine_Favourites_Block_Customer_Favourites extends Submarine_Favourites_Block_Abstract
{
    /*
     * List of product type configuration to render options list
     */
    protected $_optionsCfg = array();

    /*
     * Constructor of block - adds default renderer for product configuration
     */
    public function _construct()
    {
        parent::_construct();
        $this->addOptionsRenderCfg('default', 'catalog/product_configuration', 'favourites/options_list.phtml');
    }

    /**
     * Add favourites conditions to collection
     *
     * @param  Submarine_Favourites_Model_Mysql4_Item_Collection $collection
     * @return Submarine_Favourites_Block_Customer_Favourites
     */
    protected function _prepareCollection($collection)
    {
        $collection->setInStockFilter(true)->setOrder('added_at', 'ASC');
        return $this;
    }

    /**
     * Preparing global layout
     *
     * @return Submarine_Favourites_Block_Customer_Favourites
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__('My Favourites'));
        }
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
    }

    /**
     * Sets all options render configurations
     *
     * @param null|array $optionCfg
     * @return Submarine_Favourites_Block_Customer_Favourites
     */
    public function setOptionsRenderCfgs($optionCfg)
    {
        $this->_optionsCfg = $optionCfg;
        return $this;
    }

    /**
     * Returns all options render configurations
     *
     * @return array
     */
    public function getOptionsRenderCfgs()
    {
        return $this->_optionsCfg;
    }

    /*
     * Adds config for rendering product type options
     * If template is null - later default will be used
     *
     * @param string $productType
     * @param string $helperName
     * @param null|string $template
     * @return Submarine_Favourites_Block_Customer_Favourites
     */
    public function addOptionsRenderCfg($productType, $helperName, $template = null)
    {
        $this->_optionsCfg[$productType] = array('helper' => $helperName, 'template' => $template);
        return $this;
    }

    /**
     * Returns html for showing item options
     *
     * @param string $productType
     * @return array|null
     */
    public function getOptionsRenderCfg($productType)
    {
        if (isset($this->_optionsCfg[$productType])) {
            return $this->_optionsCfg[$productType];
        } elseif (isset($this->_optionsCfg['default'])) {
            return $this->_optionsCfg['default'];
        } else {
            return null;
        }
    }

    /**
     * Returns html for showing item options
     *
     * @param Submarine_Favourites_Model_Item $item
     * @return string
     */
    public function getDetailsHtml(Submarine_Favourites_Model_Item $item)
    {
        $helper =  Mage::helper('favourites/configuration');
        if (!($helper instanceof Mage_Catalog_Helper_Product_Configuration_Interface)) {
            Mage::throwException($this->__("Helper for favourites options rendering doesn't implement required interface."));
        }

        $block = $this->getChild('item_options');
        if (!$block) {
            return '';
        }

        $bundleOptions = $helper->getBundleOptions($item);

        return $block->setTemplate('favourites/options_list.phtml')
            ->setOptionList($bundleOptions)
            ->toHtml();
    }

    /**
     * Returns default description to show in textarea field
     *
     * @param Submarine_Favourites_Model_Item $item
     * @return string
     */
    public function getCommentValue(Submarine_Favourites_Model_Item $item)
    {
        return $this->hasDescription($item) ? $this->getEscapedDescription($item) : Mage::helper('favourites')->defaultCommentString();
    }

    /**
     * Returns qty to show visually to user
     *
     * @param Submarine_Favourites_Model_Item $item
     * @return float
     */
    public function getAddToCartQty(Submarine_Favourites_Model_Item $item)
    {
        $qty = $this->getQty($item);
        return $qty ? $qty : 1;
    }
}
