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
 * @author     Mattias Bomelin <mattias@bomelin.com>
 */

class Kontilint_News_Block_Adminhtml_News_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('news_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('knews')->__('Item Information'));
    }
    
    protected function _beforeToHtml()
    {
        
        $this->addTab('main_section', array(
            'label'     =>  Mage::helper('knews')->__('Item Information'),
            'title'     =>  Mage::helper('knews')->__('Item Information'),
            'content'   =>  $this->getLayout()->createBlock('knews/adminhtml_news_edit_tab_main')->toHtml(),
        ));
        
        $this->addTab('meta_section', array(
            'label'     =>  Mage::helper('knews')->__('Meta Data'),
            'title'     =>  Mage::helper('knews')->__('Meta Data'),
            'content'   =>  $this->getLayout()->createBlock('knews/adminhtml_news_edit_tab_meta')->toHtml(),
        ));
    
        return parent::_beforeToHtml();
        
    }
    
}
