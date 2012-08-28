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
     *          max => integer,         // Max number of elements found, if presents when get max elements stop the filter. Default is 1000
     *          deep => boolean,        // Define if search includes children or not. Default is true 
     *          dir => 'up'|'down',     // Define if analises is based in children (down) or parent (up). Default is down
     *          until => Tag            // Define an element to stop cicle. Default is null
     * 
     * @return array List of elements that matches selection
     */
    public static function search($targets, $selector, $config=array()) {
        if ($targets instanceof pQryObj)
            $targets = $targets->toArray();
        else if ($targets instanceof pQryTag)
            $targets = array($targets);
        return $targets;
        $defaults = array('max'=>1000, 'deep'=>true, 'dir'=>'down', 'until'=>null, 'stop'=>false);
        foreach ($defaults as $id => $vl) {
            if (empty($config[$id]))
                $config[$id] = $vl;
        }
        
        $return = array();
        if (self::isSelector($selector)) {
            $return = self::prepareList($targets, $config);
            
            // Supported Selectors:
            //      *, tagName, .class, #id, [attribute], [attribute=value], :input, :button,
            //      parent > child, ancestor descendant, :empty, multiple with coma
            $specialchars = array('*', '.', '#', '[', ']', ':', '>', ' ', '=');
            $rules = array();
            $selectors = explode(',', $selector);
            foreach ($selectors as $i => $slt) {
                $rules[$i] = array();
                $slt = trim(str_replace(array('"', "'"), '', $slt));
                
            }
        }
        
        return $return;
    }
    
    /**
     * Extract filters based in $config
     * @param array $targets List of Tag
     * @param array $config Based in config array used in self::search
     * @param array $container List of elements in list
     * @return array Return $container - List of elements in list
     */
    protected  static function prepareList($targets, $selector, &$config, $container=array()) {
        foreach ($targets as $obj) {
            if ($config['stop']) break;
            if ($obj == $config['until'] || $config['max'] == count($container)) {
                $config['stop'] = true;
                break;
            }
            
            if ($obj->match($selector))
                $container[] = $obj;
            
            if ($config['deep']) {
                if ($config['dir'] == 'down') {
                    $container = self::prepareList($obj->children()->toArray(), $selector, $config, $container);
                }
                else {
                    $container = self::prepareList($obj->parent()->toArray(), $selector, $config, $container);
                }
            }
        }
        return $container;
    }
}
