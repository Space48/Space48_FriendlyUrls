<?php
/**
 * Created by PhpStorm.
 * User: chesuch
 * Date: 24/11/2014
 * Time: 10:25
 */
class Space48_FriendlyUrls_Model_System_Config_Source_Attributes
{

    private function getEntityAttributes()
    {
        $attrCollection = Mage::getModel('space48friendlyurls/attributes')->getCollection();
        $attrCollection->getSelect()
            ->group('attribute_name');
        return $attrCollection;
    }

    public function toOptionArray()
    {
        $attrArray = array();
        $attributes = $this->getEntityAttributes();
        foreach($attributes as $attribute) {
            $attrValue = array(
                'value' => $attribute->getData('attribute_name'),
                'label'=>Mage::helper('adminhtml')->__(str_replace('_', ' ', $attribute->getData('attribute_name')))
            );
            array_push($attrArray, $attrValue);
        }

        return $attrArray;
    }

    public function toArray()
    {
        $attrArray = array();
        $attributes = $this->getEntityAttributes();
        foreach($attributes as $attribute) {
            $attrValue = array(
                $attribute->getData('attribute_name') => Mage::helper('adminhtml')->__(str_replace('_', ' ', $attribute->getData('attribute_name'))),
            );
            array_push($attrArray, $attrValue);
        }

        return $attrArray;
    }
}