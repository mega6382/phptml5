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
}
