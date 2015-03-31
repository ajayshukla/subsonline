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

class Kontilint_News_Block_Adminhtml_News_Edit_Tab_Meta extends Mage_Adminhtml_Block_Widget_Form
{
    
    
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('meta_fieldset', array('legend' => Mage::helper('knews')->__('Meta Data')));
             
    	$fieldset->addField('meta_keywords', 'editor', array(
            'name'		=> 'meta_keywords',
            'label'		=> Mage::helper('knews')->__('Keywords'),
            'title'		=> Mage::helper('knews')->__('Meta Keywords'),
    		'required'	=> false
        ));

    	$fieldset->addField('meta_description', 'editor', array(
            'name'		=> 'meta_description',
            'label'		=> Mage::helper('cms')->__('Description'),
            'title'		=> Mage::helper('cms')->__('Meta Description'),
    		'required'	=> false
        ));
        
        if ( Mage::getSingleton('adminhtml/session')->getNewsData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getNewsData());
            Mage::getSingleton('adminhtml/session')->setNewsData(null);
        } elseif ( Mage::registry('news_data') ) {
            $form->setValues(Mage::registry('news_data')->getData());
        }
        
        
        return parent::_prepareForm();
        
    }
    
  
}
