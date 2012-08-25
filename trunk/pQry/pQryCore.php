<?php

/**
 * Static class to make select in elements
 *
 * @author Adriano_2012
 */
class pQryCore {
    
    /**
     * Verify if $selector parameter is a valid selector
     * @param string $selector
     * @return boolean
     */
    public static function isSelector($selector) {
        if (empty($selector))
            return false;
        else
            return true;
    }
    
    /**
     * Select elements based in $selector and $config
     * @param mixed $targets - Array or Tags object list
     * @param string $selector - String with some valid selector
     * @param array $config - Associative array with options. 
     *      Keys:
     *          max => integer,         // Max number of elements found, if presents when get max elements stop the filter. Default is count($targets)
     *          deep => boolean,        // Define if search includes children or not. Default is true 
     *          dir => 'up'|'down',     // Define if analises is based in children (down) or parent (up). Default is down
     *          until => Tag            // Define an element to stop cicle. Default is null
     * 
     * @return array List of elements that matches selection
     */
    public static function run($targets, $selector, $config=array()) {
        if ($targets instanceof pQryObj)
            $targets = $targets->toArray();
        
        if (self::isSelector($selector)) {
            
        }
        
        return $targets;
    }
    
}
