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

class Kontilint_News_Block_Adminhtml_News_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('newsGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('knews/news')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    
    protected function _prepareColumns()
    {

        $this->addColumn('title', array(
            'header'    =>  Mage::helper('knews')->__('Title'),
            'align'     =>  'left',
            'index'     =>  'title',
        ));
        $this->addColumn('identifier', array(
            'header'    =>  Mage::helper('knews')->__('Identifier'),
            'align'     =>  'left',
            'index'     =>  'identifier',
        ));
    
        $this->addColumn('description', array(
            'header'    =>  Mage::helper('knews')->__('Description'),
            'align'     =>  'left',
            'index'     =>  'description',
        ));
        
        $this->addColumn('date', array(
            'header'    =>  Mage::helper('knews')->__('Date'),
            'align'     =>  'left',
            'index'     =>  'date',
            'type'      =>  'date'
        ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        =>  Mage::helper('knews')->__('Store View'),
                'index'         =>  'store_id',
                'type'          =>  'store',
                'store_all'     =>  true,
                'store_view'    =>  true,
                'sortable'      =>  false,
                'filter_condition_callback' =>  array($this, '_filterStoreCondition'),
            ));
        }
        
        $this->addColumn('is_active', array(
            'header'    =>  Mage::helper('knews')->__('Status'),
            'align'     =>  'left',
            'width'     =>  '80px',
            'index'     =>  'is_active',
            'type'      =>  'options',
            'options'   =>  array(
                1   =>  Mage::helper('knews')->__('Enabled'),
                2   =>  Mage::helper('knews')->__('Disabled'),
            ),
        ));
        
        $this->addColumn('action', array(
            'header'    =>  Mage::helper('knews')->__('Action'),
            'width'     =>  '100',
            'type'      =>  'action',
            'getter'    =>  'getId',
            'actions'   =>  array(
                array(
                    'caption'   =>  Mage::helper('knews')->__('Edit'),
                    'url'       =>  array('base'=> '*/*/edit'),
                    'field'     =>  'id'
                )
            ),
            'filter'    =>  false,
            'sortable'  =>  false,
            'index'     =>  'stores',
            'is_system' =>  true,
        ));
    
        $this->addExportType('*/*/exportCsv', Mage::helper('knews')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('knews')->__('XML'));
        
        return parent::_prepareColumns();
    
    }

    
    protected function _prepareMassaction()
    {
        
        $this->setMassactionIdField('news_id');
        $this->getMassactionBlock()->setFormFieldName('news');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'     =>  Mage::helper('knews')->__('Delete'),
            'url'       =>  $this->getUrl('*/*/massDelete'),
            'confirm'   =>  Mage::helper('knews')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('knews/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'         =>  Mage::helper('knews')->__('Change status'),
            'url'           =>  $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional'    =>  array(
                'visibility'  =>  array(
                    'name'      =>  'status',
                    'type'      =>  'select',
                    'class'     =>  'required-entry',
                    'label'     =>  Mage::helper('knews')->__('Status'),
                    'values'    =>  $statuses
                )
            )
        ));
        
        return $this;
        
    }
    
    
    protected function _afterLoadCollection()
    {
        
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
        
    }
    
    
    protected function _filterStoreCondition($collection, $column)
    {
        
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
    
        $this->getCollection()->addStoreFilter($value);
        
    }

    
    public function getRowUrl($row)
    {
        
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        
    }

    
}
