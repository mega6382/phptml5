<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HTMLParser
 * Based in php.net code: http://docs.php.net/manual/en/class.domdocument.php#93900
 * @author Adriano_2
 */
class HTMLParser extends DOMDocument
{
    
    public function toArray(DOMNode $oDomNode = null)
    {
        // Return empty array if DOM is blank
        if (is_null($oDomNode) && !$this->hasChildNodes()) {
            return array();
        }
        
        $oDomNode = (is_null($oDomNode)) ? $this->documentElement : $oDomNode;
        
        //Attributes - get our attributes if we have any
        $arAttributes = array();
        if ($oDomNode->hasAttributes()) {
            foreach ($oDomNode->attributes as $oAttrNode) {
                // retain namespace prefixes
                $arAttributes[$oAttrNode->nodeName] = $oAttrNode->nodeValue;
            }
        }
        // check for namespace attribute - Namespaces will not show up in the attributes list
        if ($oDomNode instanceof DOMElement && $oDomNode->getAttribute('xmlns')) {
            $arAttributes["xmlns"] = $oDomNode->getAttribute('xmlns');
        }
        
        $childNodes = array();
        if ($oDomNode->hasChildNodes()) {
            foreach ($oDomNode->childNodes as $oChildNode) {
                if ($oChildNode->nodeName{0} == '#') {
                    // Text
                    $childNodes[] = $oChildNode->nodeValue;
                }
                else {
                    // Element
                    $childNodes[] = $this->toArray($oChildNode);
                }
            }
        }
        
        return array('tag' => $oDomNode->nodeName, 
                     'childNodes'=>$childNodes, 
                     'attributes'=>$arAttributes);
    }
}
