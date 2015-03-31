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

class Kontilint_News_Block_Adminhtml_News_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    
    
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('main_fieldset', array('legend' => Mage::helper('knews')->__('Item information')));
     
    	$fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('knews')->__('Title'),
            'title'     => Mage::helper('knews')->__('Title'),
            'required'  => true,
        ));
        
    	$fieldset->addField('identifier', 'text', array(
            'name'      => 'identifier',
            'label'     => Mage::helper('knews')->__('SEF URL Identifier'),
            'title'     => Mage::helper('knews')->__('SEF URL Identifier'),
            'required'  => true,
            'class'     => 'validate-identifier',
            'after_element_html' => '<p class="nm"><small>' . Mage::helper('knews')->__('(eg: domain.com/identifier)') . '</small></p>',
        ));
        
    	$fieldset->addField('date', 'date', array(
            'name'      => 'date',
            'label'     => Mage::helper('knews')->__('Date'),
            'title'     => Mage::helper('knews')->__('Date'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required'  => true,
        ));
        
		$fieldset->addField('store_id','multiselect',array(
			'name'      => 'stores[]',
            'label'     => Mage::helper('knews')->__('Store View'),
            'title'     => Mage::helper('knews')->__('Store View'),
            'required'  => true,
			'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
		));

    	$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('knews')->__('Status'),
            'title'     => Mage::helper('knews')->__('News Status'),
            'name'      => 'is_active',
            'required'  => true,
	        'options'   => array(
	            1 => Mage::helper('knews')->__('Enabled'),
	            2 => Mage::helper('knews')->__('Disabled'),
	        ),
        ));
	$fieldset->addField('image', 'image', array(
		'name' => 'image',
		'label' => Mage::helper('knews')->__('Image'),
		'title' => Mage::helper('knews')->__('Image'),
		'required' => false,
	));

	$fieldset->addField('category', 'select', array(
		'name' => 'category',
		'label' => Mage::helper('knews')->__('Category'),
		'title' => Mage::helper('knews')->__('Category'),
		'values' => Mage::getModel('knews/news')->getCategorys(),
		'required' => true,
	));
        
	$fieldset->addField('short_description', 'editor', array(
		'name' => 'short_description',
		'label' => Mage::helper('knews')->__('Short description'),
		'title' => Mage::helper('knews')->__('Short description'),
		'style' => 'height:12em;width:500px;',
		'wysiwyg' => false,
		'required' => true,
	));
    	$fieldset->addField('description', 'editor', array(
            'name'      => 'description',
            'label'     => Mage::helper('knews')->__('Description'),
            'title'     => Mage::helper('knews')->__('Description'),
            'style'     => 'height:12em;width:500px;',
            'wysiwyg'   => false,
            'required'  => true,
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
