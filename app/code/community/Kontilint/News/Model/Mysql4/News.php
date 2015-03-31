<?php

class Kontilint_News_Model_Mysql4_News extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('knews/news', 'news_id');
    }
    
    
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
    	
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('news_store'))
            ->where('news_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
/*
		$row['_image'] = '';
		if ($row['image'] != '') {
			$imgobj = new Varien_Image(Mage::getBaseDir() . DS . $row['image']);
			$imgobj->constrainOnly(TRUE);
			$imgobj->keepAspectRatio(TRUE);
			$row['_image'] = $imgobj;
		}
*/
            }
            $object->setData('store_id', $storesArray);
        }

        return parent::_afterLoad($object);
        
    }
    

    /**
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {

        $value = $object->getData('image');
        $path = Mage::getBaseDir('media') . DS;
        if (is_array($value) && !empty($value['delete'])) {
                    //remove the file
                    unlink(Mage::getBaseDir('media') . DS . $value['value']);
                    $object->setData('image', '');
        } elseif (is_array($value) && isset($value['value'])) {
            $object->setData('image', $value['value']);
        } else
                try {
                        //try to make the uplaod
                    $uploader = new Varien_File_Uploader('image');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->save($path);

                    $object->setData('image', $uploader->getUploadedFileName());
                } catch (Exception $e) {
                }



        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        foreach (array('date') as $dataKey) {
            if ($date = $object->getData($dataKey)) {
                $object->setData($dataKey, Mage::app()->getLocale()->date($date, $format, null, false)
                    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
                );
            }
            else {
                $object->setData($dataKey, new Zend_Db_Expr('NULL'));
            }
        }
        return $this;
    }
    
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        
        $condition = $this->_getWriteAdapter()->quoteInto('news_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('news_store'), $condition);
    
        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['news_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert($this->getTable('news_store'), $storeArray);
        }
    
        return parent::_afterSave($object);
        
    }

/**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param   string $identifier
     * @param   int $storeId
     * @return  int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $select = $this->_getReadAdapter()->select()->from(array('main_table'=>$this->getMainTable()), 'news_id')
            ->join(
                array('store_table' => $this->getTable('news_store')),
                'main_table.news_id = store_table.news_id'
            )
            ->where('main_table.identifier = ?', $identifier)
            ->where('main_table.is_active = 1')
            ->where('store_table.store_id in (?) ', array(0, $storeId))
            ->order('store_table.store_id DESC');
            

        return $this->_getReadAdapter()->fetchOne($select);
    }
	public function checkCategoryIdentifier($identifier, $storeId) {
		$select = $this->_getReadAdapter()->select()->from($this->getTable('news_cat'))
		->where('url_key = ?', $identifier);
		return $this->_getReadAdapter()->fetchOne($select);
	}
	public function getCategorys() {
		$categorys = array();
		$select = $this->_getReadAdapter()->select()
			->from($this->getTable('news_cat'));
		if ($data = $this->_getReadAdapter()->fetchAll($select)) {
			foreach ($data as $row) {
				$categorys[] = array('label' => $row['label'], 'value' => $row['identifier']);
			}
		}
		return $categorys;
	}
    
    
}



