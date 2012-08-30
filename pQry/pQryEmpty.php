<?php

/**
 * EmptyTag is used as a container element
 *
 * @author Adriano_2012
 */
class pQryEmpty extends pQryTag {
    protected function getAttributeList() {
        return array();
    }

    protected function getTagName() {
        return '';
    }

    protected function hasEndtag() {
        return true;
    }
    
    public function attr($nameOrList, $value = null) {
        if (is_null($value) && is_string($nameOrList)) return '';
        else return $this;
    }
    
    protected function insertIn($content, $index) {
        return $this;
    }
    
    protected function setParent($parent) {
        return $this;
    }
    
    public function toArray() {
        return array();
    }
}
