<?php

/**
 * Static class to make select in elements
 *
 * @author Adriano_2012
 */
class pQryCore {
    /**
     * Contais an empty pQueryObject (singleton pattern)
     * @var pQryEmpty
     */
    private static $empty;
    
    /**
     * Contains the root element defined by user
     * @var pQryTag
     */
    private static $context;
    
    /**
     * Associative array with string selector as key and rules as values
     * @var array 
     */
    private static $selectors = array();
    
    /**
     * Return the Empty object
     * @return pQryEmpty
     */
    public static function getEmptyObject() {
        if (!self::$empty) {
            self::$empty = new pQryEmpty();
        }
        return self::$empty;
    }
    
    /**
     * Define a default root element
     * @param pQryTag $defaultContext
     * @return pQryTag
     */
    public static function setDefaultContext(pQryTag $defaultContext) {
        self::$context = pQry($defaultContext);
        return self::$context;
    }
    
    /**
     * Return the default root element, if it don't set yet, return empty element
     * @return pQryTag
     */
    public static function getDefaultContext() {
        if (!self::$context)
            return self::getEmptyObject();
        else
            return self::$context;
    }
    
    /**
     * Remove extra attributes and quotes
     * @param string $selector CSS3 selector
     * 
     * @return string new CSS3 selector
     */
    protected static function cleanSelector($selector) {
        return preg_replace('/ {2,}/',' ',trim(str_replace(array('"', "'"), '', $selector)));
    }
    
    /**
     * Verify if $selector parameter is a valid selector
     * @param string $selector CSS3 selector
     * 
     * @return boolean true if is valid false otherwise
     */
    public static function isSelector($selector) {
        if (empty($selector))
            return false;
        else {
            $selector = self::cleanSelector($selector);
            if (!empty(self::$selectors[$selector])) return true;
            $rules = array();
            $selectors = explode(',', $selector);
            foreach ($selectors as $slt) {
                $rule = array();
                $slt = trim($slt);
                $tam = strlen($slt);
                $cursor = 0;
                while ($cursor < $tam) {
                    switch ($slt[$cursor]) {
                        case '*':
                            $rule['tag'] = '*';
                            $cursor++;
                            break;
                        case '.':
                            $ini = ++$cursor;
                            while($cursor < $tam) {
                                if (ctype_alnum($slt[$cursor])) $cursor++;
                                else break;
                            }
                            $rule['class'] = substr($slt, $ini, $cursor - $ini);
                            break;
                        case '#':
                            $ini = ++$cursor;
                             while($cursor < $tam) {
                                if (ctype_alnum($slt[$cursor])) $cursor++;
                                else break;
                            }
                            $rule['id'] = substr($slt, $ini, $cursor - $ini);
                            break;
                        case '[':
                            $ini = ++$cursor;
                            while($cursor < $tam) {
                                if($slt[$cursor] == ']') break;
                                else $cursor++;
                            }
                            $attrval = substr($slt, $ini, $cursor - $ini);
                            if (strlen($attrval)<3) return false;
                            if (empty($rule['attr'])) $rule['attr'] = array();
                            $pos = strpos($attrval, '=');
                            
                            // TODO test if invalid operator
                            if ($pos === false)
                                $rule['attr'][] = $attrval;
                            else {
                                if (ctype_alnum($attrval[$pos-1])) {
                                    list($name, $val) = explode('=', $attrval);
                                    $op = '=';
                                }
                                else {
                                    $name = substr($attrval, 0, $pos-1);
                                    $op = substr($attrval, $pos-1, 2);
                                    $val = substr($attrval, $pos+1);
                                }
                                $rule['attr'][$name] = array('op'=>$op, 'value'=>$val);
                            }
                            $cursor++;
                            break;
                        case ':':
                            // TODO test if pseudo is valid
                            $ini = ++$cursor;
                            while($cursor < $tam) {
                                if(ctype_alnum($slt[$cursor])) $cursor++;
                                else break;
                            }
                            if (empty($rule['pseudo'])) $rule['pseudo'] = array();
                            $rule['pseudo'][] = substr($slt, $ini, $cursor - $ini);
                            break;
                        case ' ':
                            //descendant
                            if (in_array($slt[$cursor+1], array('>','+','~'))) {
                                $cursor++;
                                break;
                            }
                            $key = 'descendant';
                        case '>':
                            //child
                            if (empty($key)) $key = 'child';
                        case '+':
                            //next   
                            if (empty($key)) $key = 'next';
                         case '~':
                            //siblings
                            if (empty($key)) $key = 'siblings';
                             
                            $newsel = substr($slt, $cursor+1);
                            if (self::isSelector($newsel)) {
                                $r = self::getRules($newsel);
                                $rule[$key] = $r[0];
                            } else
                                return false;
                            $tam = 0;
                            break;
                        default:
                            $ini = $cursor++;
                            while($cursor < $tam) {
                                if (ctype_alnum($slt[$cursor])) $cursor++;
                                else break;
                            }
                            $tag = substr($slt, $ini, $cursor - $ini);
                            if (empty($tag)) return false;
                            $rule['tag'] = $tag;
                    }
                }
                if (count($rule))
                    $rules[] = $rule;
                else
                    return false;
            }
            
            self::$selectors[$selector] = $rules;
            return true;
        }
    }
    
    /**
     * Return the rule to filter based in selector
     * @param string $selector CSS3 selector
     * 
     * @return array An array with rules. Each rules may contains the keys:     
     *      tag => string
     *      class => string
     *      id => string
     *      attr => array(attributename, attributename=>array('op'=>operator, 'value'=>attributevalue), ...)
     *      pseudo => array(string, ...)
     *      descendant => [rule]
     *      next => [rule]
     *      child => [rule]
     *      siblings => [rule]
     */
    public static function getRules($selector) {
        if (self::isSelector($selector))
            return self::$selectors[self::cleanSelector($selector)];
        else
            return array();
    }
    
    /**
     * Select elements based in $selector and $config
     * @param mixed $targets - Array or Tags object list
     * @param mixed $selector
     *      String with some valid selector<br>
     *      array of rules
     * @param array $config - Associative array with options. 
     *      Keys:
     *          max => integer,         // Max number of elements found, if presents when get max elements stop the filter. Default is 1000
     *          deep => boolean,        // Define if search includes children or not. Default is true 
     *          dir => 'up'|'down',     // Define if analises is based in children (down) or parent (up). Default is down
     *          until => Tag            // Define an element to stop cicle. Default is null
     * 
     * @return array List of elements that matches selection
     */
    public static function select($targets, $selector, $config=array()) {
        if (is_object($targets))
            $targets = $targets->toArray();
        
        $defaults = array('max'=>5000, 'deep'=>true, 'dir'=>'down', 'until'=>null, 'stop'=>false);
        foreach ($defaults as $id => $vl) {
            if (!array_key_exists($id, $config))
                $config[$id] = $vl;
        }
        
        if ((is_string($selector) && self::isSelector($selector)))
            $rules = self::getRules($selector);
        else if(is_array($selector))
            $rules = array($selector);
        
        if (!empty($rules)) {
            return array_unique(self::executeRules($targets, $rules, $config)); 
        }
        return array();
    }
    
    /**
     * Execute rules to filter elements
     * @param array $targets List of Tag
     * @param array $rules List of rules to filter $targets
     * @param array $config Based in config array
     * 
     * @return array List of elements Tag that complies with the rules and settings
     */
    protected  static function executeRules($targets, $rules, &$config, $container=array()) {
        foreach ($targets as $obj) {
            if ($config['stop']) break;
            if ($obj == $config['until'] || $config['max'] == count($container)) {
                $config['stop'] = true;
                break;
            }
            
            foreach ($rules as $rule) {
                if ($obj->match($rule)) {
                    $container[] = $obj;
                    break;
                }
            }
            
            if ($config['deep']) {
                if ($config['dir'] == 'down') {
                    $container = self::executeRules($obj->children()->toArray(), $rules, $config, $container);
                }
                else {
                    $container = self::executeRules($obj->parent()->toArray(), $rules, $config, $container);
                }
            }
        }
        return $container;
    }
}
