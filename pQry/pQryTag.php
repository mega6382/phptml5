<?php
/**
 * This class represents an abstract HTML element
 * In this implementation the Tag is based in $.fn jQuery object
 * Otherwise it doesn't support direct instance
 *
 * @author Adriano_2012
 */
abstract class pQryTag {
    /**
     * Referente to parent element
     * @var pQryTag 
     */
    protected $parent;
    
    /**
     * Array of childs
     * Contains string elements (just text) or Tag elements
     * 
     * @var array - Array of string and Tag
     */
    protected $content = array();
    
    /**
     * Associative array for attributes.
     * Key is attribute name and value is attribute value
     * 
     * @var array - Array of strings. Ex: array('attributeName'=>'AttributeValue');
     */
    protected $attributes = array();
    
    /* Abstract methods */
    
    /**
     * Return element tag name
     * 
     * @return string
     */
    protected abstract function getTagName();
    
    /**
     * Return if element has closing tag or not
     * 
     * @return boolean - TRUE if element has closing tag and FALSE otherwise
     */
    protected abstract function hasEndtag();
    
    /**
     * Return attribute list for this element without commom attributes
     * 
     * @return array - Array of string wich attribute names valid for the element
     */
    protected abstract function getAttributeList();
    
    /* Protected methods */
    /**
     * Insert the content(s) in specific position, when position is invalid adding on end list
     * @param mixed $content Element/Elements to be add. HTML String or Tag element
     * @param integer $index Order to be adding
     * @return \pQryTag
     */
    protected function insertIn($content, $index) {
        if ($index < 0 || $index > count($this->content)) {
            $index = count($this->content);
        }
        $contents = $this->parseInternal($content);
        $list = array();
        foreach ($contents as $content) {
            if ($content instanceof pQryTag) {
                $content->setParent($this);
            }
            $list[] = $content;
        }
        $this->content = array_merge(array_slice($this->content, 0, $index), $list, array_slice($this->content, $index));
        return $this;
    }
       
    /**
     * Clean text without html tags, extra spaces and escape special characters
     * @param string $text Text to be converted
     * 
     * @return string New text
     */
    protected function cleanText($text) {
        return trim(htmlspecialchars(strip_tags($text))); 
    }
    
    /**
     * Clean text without html tags, extra spaces, escape special characters and convert to lowercase
     * @param string $text Text to be converted
     * 
     * @return string New text
     */
    protected function cleanAttr($text) {
        return preg_replace("/&#?[a-z0-9]{2,8};/i","",strtolower($this->cleanText($text)));
    }
    
    /**
     * Parse elements when necessary. If text hasn't html mackup nothing is done
     * @param mixed $content 
     *      String HTML
     *      Tag or Tags element
     *      function(Tag)
     *      Simple text
     * 
     * @return array List of string and/or Tag
     */
    protected function parseInternal($content) {
        if (is_callable($content))
            $content = $this->parseInternal($content($this));
        
        if ($content instanceof pQryTag) {
            if ($content instanceof pQryEmpty) return $content->content;
            else return array($content);
        }
        else if($content instanceof pQryObj)
            return $content->toArray();
        
        if (strlen($content) == strlen(strip_tags($content)))
            return array($content);
        else
            return self::parse($content);
    }

    /**
     * Set the parent to element
     * If element alread has parent, it will be changed
     * 
     * @param \pQryTag $parent New Parent
     * @return \pQryTag
     */
    protected function setParent($parent) {
        if ($parent == null) {
            $this->parent = null;
        }
        else {
            if (!is_null($this->parent))
                $this->parent->remove($this);
            if($parent->parent() == $this)
                $this->remove($parent);
            $this->parent = $parent;
        }
    }
    
    /* Static methods */
    /**
     * Convert html string in element list
     * @param string $html Content html to be parsed
     * 
     * @return array List of Tag
     */
    public static function parse($html) {
        $doc = new HTMLParser();
        $doc->strictErrorChecking = false;
        $doc->loadHTML($html);   
        $node = $doc->toArray();
        
        //Search for first tag
        $tag = '';
        $caracs = array(' ','<','>');
        $open = false;
        for($i=0; $i < strlen($html); $i++) {
            $c = $html[$i];
            if (in_array($c, $caracs) && $open) break;
            if (in_array($c, $caracs)) continue;
            $tag .= $c;
            $open = true;
        }
        $tag = strtolower($tag);
        $nodes = array();
        if ($tag == 'html') {
            $nodes[] = $node;
        }
        else if ($tag == 'body' || $tag == 'head') {
            $nodes = $node['childNodes'];
        }
        else {
            $nodes = $node['childNodes'][0]['childNodes'];
        }
        
        $list = array();
        foreach ($nodes as $node) {
            $list[] = self::parseNode($node);
        }
        
        return $list;
    }
    
    /**
     * Recursive function used in parser
     * @param array $node Array used in HtmlParser::toArray
     * @return \pQryTag
     */
    protected static function parseNode($node) {
        // Create obj
        $tagName = ucfirst($node['tag']);
        if (class_exists($tagName)) {
            $obj = new $tagName();
        }
        else {
            $obj = new pQryHTML($tagName);
        }

        // Set Attributes
        if (count($node['attributes'])) {
            $obj->attr($node['attributes']);
        }

        // Parse Content
        foreach($node['childNodes'] as $content) {
            if (is_string($content)) {
                $obj->append(strip_tags($content));
            }
            else {
                $obj->append(self::parseNode($content));
            }
        }
        
        return $obj;
    }
    
    /* Magic Methods */    
    /**
     *  Create a deep copy of the set of matched elements.
     */
    public function __clone() {
        $this->parent = null;
        $contents = $this->content;
        $this->content = array();
        foreach ($contents as $content) {
            if ($content instanceof pQryTag)
                $this->append(clone $content);     
            else
                $this->append ($content);
        }
    }
    
    /**
     * @see Tag::toString
     * @return string
     */
    public function __toString() {
        return $this->toString();
    }
    
    /* Public methods based in jQuery */
    /**
     * Add elements to the set of matched elements
     * Same as append
     * @see Tag::append
     */
    public function add($content) {
       $this->append($content);
    }
    
    /**
     * Adds the specified class(es) to each of the set of matched elements.
     * @param mixed $classNameOrFunction 
     *      string - One or more class names to be added to the class attribute of each matched element.
     *      function(Tag, currentClass) - A function returning one or more space-separated class names to be added to the existing class name(s). 
     *          Receives the element in the set and the existing class name(s) as arguments.
     * 
     * @return pQryTag - The object reference
     */
    public function addClass($classNameOrFunction) {
        if (is_callable($classNameOrFunction))
            $className = $classNameOrFunction($this, $this->attr('class'));
        else
            $className = $classNameOrFunction;
        
        if (strlen($this->attr('class'))>0)
            $className = ' ' . $className;
        
        $classes = explode(' ', preg_replace('!\s+!', ' ', $this->attr('class') . $className));
        $this->attr('class', implode(array_unique($classes), ' '));
        return $this;
    }
    
    /**
     * Insert content, specified by the parameter, after this element
     * @param mixed $content Accept one or more additional content
     *      HTML string, 
     *      Tag or Tags object, 
     *      function function(Tag) to insert after this element.
     *      Array of all types
     * 
     * @return \pQryTag - The object reference
     */
    public function after($contents=array()) {
        $index =  $this->index()+1;
        
        if (!is_array($contents) || !count($contents)) 
            $contents = func_get_args();
        rsort($contents);
        
        $father = $this->parent();
        foreach ($contents as $content) {
            $father->insertIn($content, $index);
        }
        return $this;
    }
    
    /**
     * Just return an instance of this
     */
    public function andSelf() {
        return $this;
    }
    
    /**
     * Insert content, specified by the parameter, to the end in the set of contents.
     * @param mixed $content Accept one or more additional content
     *      HTML string, 
     *      Tag or Tags object, 
     *      function function(Tag) to append in this element.
     *      Array of all types
     * 
     * @return \pQryTag - The object reference
     */
    public function append($contents=array()) {
        if (!is_array($contents) || !count($contents)) 
            $contents = func_get_args();
        
        foreach ($contents as $content) {
            $this->insertIn($content, count($this->content));
        }
        return $this;
    }
    
    /**
     * Insert every element in the set of matched elements to the end of the target.
     * @param mixed $target Tag or Tags object,
     * 
     * @return \pQryTag - The object reference
     */
    public function appendTo($target) {
        $target->append($this);
    }
    
    /**
     * Get the value of an attribute for the first element in the set of matched elements.
     * Set one or more attributes for the set of matched elements 
     * @param mixed $nameOrList 
     *      array associative with attribute names and values,
     *      string Name or attribute,
     *      function(Tag, nameattr) - A function returning one string value to be added to the existing attr
     * @param string $value atribute value
     * 
     * @return mixed In set return a \Tag object reference, in get return string value of atribute
     */
    public function attr($nameOrList, $value=null) {
        if (is_null($value) && is_string($nameOrList)) {
            // Metodo Get
            $attr = $this->cleanAttr($nameOrList);
            if (empty($this->attributes[$attr]))
                return "";
            return $this->attributes[$attr];
        }
        else {
            // Metodo Set
            if (is_string($nameOrList)) {
                if (is_callable($value)) {
                    $oldval = $value;                    
                    $value = @ $value($this, $nameOrList);
                    if ($value == null || $value == '')
                        $value = $oldval;
                }
                $nameOrList = array($nameOrList => $value);
            }
            foreach ($nameOrList as $attr => $val) {
                $this->attributes[$this->cleanAttr($attr)] = $this->cleanText($val);
            }
            return $this;
        }
    }
    
    /**
     * Insert content, specified by the parameter, before each element in the set of matched elements.
     * @param mixed $content Accept one or more additional content
     *      HTML string, 
     *      Tag or Tags object, 
     *      function function(Tag) to insert after this element.
     *      Array of all types
     * 
     * @return \pQryTag - The object reference
     */
    public function before($contents=array()) {
        $father = $this->parent();
        $index =  $father->index($this);
        
        if (!is_array($contents) || !count($contents)) 
            $contents = func_get_args();
        rsort($contents);
        foreach ($contents as $content) {
            $father->insertIn($content, $index);
        }
        return $this;
    }
    
    /**
     * Get the children of each element in the set of matched elements, optionally filtered by a selector.
     * 
     * @return pQryObj List of children
     * @toto implements selector
     */
    public function children($selector=null) {
        if (is_null($selector)) $selector = "*";
        $list = array();
        foreach ($this->content as $content) {
            if ($content instanceof pQryTag)
                $list[] = $content;
        }
        return new pQryObj(pQryCore::search($list, $selector, array('deep'=>false)), $this);
    }
    
    /**
     * Create a deep copy of the set of matched elements.
     * @param boolean $withAttr=true A Boolean indicating whether attributes should be copied along with the elements.
     * @param boolean $withContent=true A Boolean indicating whether data content should be copied along with the elements.
     * 
     * @return \pQryTag The new object reference
     */
    public function cloneThis($withAttr=true, $withContent=true) {
        $obj = clone $this;
        if (!$withAttr)
            $obj->emptyAttr(true);
        if (!$withContent)
            $obj->emptyContent();
        return $obj;
    }
    
    /**
     * Get the first element that matches the selector, beginning at the current element and progressing up through the DOM tree.
     *      Begins with the current element, 
     *      Travels up the DOM tree until it finds a match for the supplied selector, 
     *      The returned jQuery object contains zero or one element
     * @param string $selector
     * 
     * @return mixed \Tag or \Tags
     */
    public function closest($selector=null) {
        $data = pQryCore::search(array($this), $selector, array('max'=>1, 'dir'=>'up'));
        if (empty($data))
            return new pQryObj($this);
        else
            return $data[0];
    }
    
    /**
     * Check to see if a Tag is within this element.
     * Text are too supported.
     * @param pQryTag $contained The Tag element that may be contained by the other element.
     * 
     * @return boolean true if contains and false otherwise
     */
    public function contains(pQryTag $contained) {
        $ret = in_array($contained, $this->content);
        if (!$ret) {
            foreach ($this->content as $obj) {
                if ($obj instanceof pQryTag) {
                    $ret = $ret || $obj->contains($contained);
                    if ($ret) break;
                }
            }
        }
        return $ret;
    }
    
    /**
     * Get the children of each element in the set of matched elements, including text
     * @return array List of contents, includes Tag and string
     */
    public function contents() {
        return $this->content;
    }
    
    /**
     * Get the value of a style property for the first element in the set of matched elements.
     * Set one or more CSS properties for the set of matched elements.
     * @param mixed $styleOrList A CSS property name. A map of property-value pairs to set.
     * @param string $value 
     *      A value to set for the property,
     *      function(Tag, namestyle) - A function returning the value to set.
     * 
     * @return mixed In set return a \Tag object reference, in get return string value of style
     * @todo What to do with url('location') because the quot?
     */
    public function css($styleOrList, $value=null) {
        if (is_null($value) && is_string($styleOrList)) {
            // Metodo Get
            $styleOrList = $this->cleanAttr($styleOrList);
            $styles = explode(';', $this->attr('style'));
            foreach($styles as $regra) {
                if (empty($regra)) continue;
                list($prop, $cssval) = explode(':', $regra, 2);
                if ($this->cleanAttr($prop) == $styleOrList)
                    return trim($cssval);
            }
            return "";
        }
        else {
            // Metodo Set
            if (is_string($styleOrList)) {
                if (is_callable($value)) {
                    $value = $value($this, $styleOrList);
                }
                $styleOrList = array($styleOrList => $value);
            }
            
            // Clean rules informed by users
            $newStyle = array();
            foreach ($styleOrList as $k => $v) {
                $newStyle[$this->cleanAttr($k)] = $this->cleanText($v);
            }
            $styleOrList = $newStyle;
            
            // Merge news and old css rules
            $style = $this->attr('style');
            $styles = array();
            if (!empty($style)) {
                foreach(explode(';', $this->attr('style')) as $regra) {
                    if (empty($regra)) continue;
                    list($prop, $cssval) = explode(':', $regra, 2);
                    $prop = trim($prop);
                    if (empty($styleOrList[$prop]))
                        $styles[$prop] = trim($cssval);
                    else
                        $styles[$prop] = $styleOrList[$prop];
                }
            } 
            $styles = array_merge($styles, $styleOrList);
            
            // Transform all rules in style
            $newStyles = array();
            foreach ($styles as $prop => $cssval) {
                $cssval = str_replace(';','',$cssval);
                if (empty($cssval)) continue;
                $newStyles[] = $prop . ':' . $cssval;
            }
            
            return $this->attr('style', implode('; ', $newStyles) . ';');
        }
    }
    
    /**
     * Store arbitrary data associated with the matched elements.
     * Returns value at named data store for the first element in the jQuery collection, as set by data(name, value).
     * @param mixed $nameOrList 
     *      A string naming the piece of data to set. Name of the data stored.
     *      An object of key-value pairs of data to update.
     * @param mixed $value
     *      The new data value; it can be any Javascript type including Array or Object.
     *      function(Tag, namedata) - A function returning the value to set.
     * 
     * @return mixed In set return a \Tag object reference, in get return string data value
     */
    public function data($nameOrList, $value=null) {
        if (is_null($value) && is_string($nameOrList)) {
            // Metodo Get
            return $this->attr('data-' . $nameOrList);
        }
        else {
            // Metodo Set
            if (is_string($nameOrList)) {
                if (is_callable($value)) {
                    $value = $value($this, $nameOrList);
                }
                $nameOrList = array($nameOrList => $value);
            }
            foreach ($nameOrList as $attr => $val) {
                $this->attr('data-' . $attr, $val);
            }
            return $this;
        }
    }
    
    /**
     * Iterate over a Tag object, executing a function for each content.
     * @param string $function Function name that receive function(index, Element)
     * @param boolean $onlyTag Determine if iterable with only Element or accept all content
     * 
     * @return \pQryTag The object reference
     */
    public function each($function, $onlyTag=false) {
        if (is_callable($function)) {
            foreach ($this->content as $i => $content) {
                if ($onlyTag && is_string($content)) continue;
                if ($function($i, $content) === false) break;
            }
        }
        return $this;
    }
    
    /**
     * Remove all child nodes and all attributes
     * 
     * @return \pQryTag The object reference
     */
    public function emptyAll() {
        return $this->emptyContent()->emptyAttr();
    }
    
    /**
     * Remove all attributes
     * @param boolean $recursive Determine if clean attributes recursive. The Default is false
     * 
     * @return \pQryTag The object reference
     */
    public function emptyAttr($recursive=false) {
        $this->attributes = array();
        if ($recursive) {
            foreach ($this->content as $content) {
                if ($content instanceof pQryTag)
                    $content->emptyAttr(true);
            }
        }
        return $this;
    }
    
    /**
     * Remove all child nodes
     * 
     * @return \pQryTag The object reference
     */
    public function emptyContent() {
        $this->content = array();
        return $this;
    }
    
    /**
     * Reduce the set of matched elements to the one at the specified index
     * @param int $index 
     *      An integer indicating the 0-based position of the element,
     *      An integer indicating the position of the element, counting backwards from the last element in the set.
     * 
     * @return mixed \Tag or empty \Tags when not found index
     */
    public function eq($index) {
        return $this->children()->eq($index);
    }
    
    /**
     * Reduce the content to those that match the selector or pass the function's test.
     * @param mixed $selectorOrFunction
     *      A string containing a selector expression to match the current set of elements against,
     *      function(content)A function used as a test for each element in the set.
     * 
     @return \pQryTag The object reference
     */
    public function filter($selectorOrFunction) {
        $this->content = $this->children()->filter($selectorOrFunction)->toArray();
        return $this;
    }
    
    /**
     * Get the descendants of each element in the current set of matched elements, filtered by a selector
     * @param string $selector A string containing a selector expression to match elements against.
     * @return \pQryObj
     */
    public function find($selector) {
        return new pQryObj(pQryCore::search($this, $selector), $this);
    }
    
    /**
     * Reduce the set of matched elements to the first in the set.
     * 
     * @return mixed \Tag or empty \Tags when not found index
     */
    public function first() {
        return $this->eq(0);
    }
    
    /**
     * Reduce the content to those that have a descendant that matches the selector or Tag.
     * @param mixed $selectorOrTag
     *      A string containing a selector expression to match elements against.
     *      A Tag element to match elements against.
     * 
     @return \pQryTag The object reference
     */
    public function has($selectorOrTag) {
        $list = array();
        if ($selectorOrTag instanceof pQryTag) {
            foreach ($this->content as $content) {
                if ($content instanceof pQryTag) {
                    if ($content->contains($selectorOrTag) || $content == $selectorOrTag)
                        $list[] = $content;
                }
            }
        }
        else {
            foreach ($this->content as $content) {
                if ($content instanceof pQryTag) {
                    if (count(pQryCore::search($content, $selectorOrTag)))
                        $list[] = $content;
                }
            }
        }
        return $this;
    }
    
    /**
     * Determine whether any of the matched elements are assigned the given class.
     * @param string $classes The class name to search for or The classes names with space between them
     * 
     * @return boolean Return true if the class is assigned to element, false otherwise
     */
    public function hasClass($classes) {
        $allClass = $this->attr('class');
        $classes = explode(' ', $classes);
        foreach ($classes as $cls) {
            if (strpos($allClass, $cls) === false)
                return false;
        }
        return true;
    }
    
    /**
     * Get the HTML contents of the Tag element in the set of matched elements.
     * Set the HTML contents of each element in the set of matched elements.
     * @param mixed $content
     *      A string of HTML to set as the content of each matched element.
     *      function(Tag) A function returning the HTML content to set.
     *      Tag or Tags objetct
     * 
     * @return mixed In set return a \Tag object reference, in get return string value of atribute
     */
    public function html($content=null) {
        if (is_null($content)) {
            // Metodo GET
            $html = "";
            foreach ($this->content as $content) {
                if ($content instanceof pQryTag) {
                    $html .= $content->toString();
                }
                else {
                    $html .= $content;
                }
            }
            return $html;
        }
        else {
            // Metodo SET
            $this->emptyContent();
            return $this->append($content);
        }
    }
    
    /**
     * Get the id attribute of the Tag element.
     * Set the id attribute of the Tag element.
     * @param string $value id Value
     * 
     * @return mixed In set return a \Tag object reference, in get return string value of atribute
     */
    public function id($idValue=null) {
        return $this->attr('id', $idValue);
    }
    
    /**
     * Return the numeric position (index) of $element in contents
     * If no argument is passed to the .index() method, the return value is an integer 
     * indicating the position of the first element within the jQuery object relative to its sibling elements.
     * 
     * @param mixed $selectorOrTag a Tag element or a selector 
     * @return int The index of element or -1 when not found
     */
    public function index($selectorOrTag=null) {
        if (is_null($selectorOrTag)) {
            return $this->parent()->index($this);
        }
        else {
            if (is_string($selectorOrTag)) {
                $list = pQryCore::search($this->children(), $selectorOrTag, array('deep'=>false, 'max'=>1));
                if (count($list))
                    $selectorOrTag = $list[0];
                else
                    return -1;
            }
            $ret = array_search($selectorOrTag, $this->content);  
            if ($ret === false)
                return -1;
            return $ret;
        }
    }
    
    /**
     * Insert every element in the set of matched elements after the target.
     * @param pQryTag $target Tag element container
     * 
     @return \pQryTag The object reference
     */
    public function insertAfter(pQryTag $target) {
        $target->after($this);
        return $this;
    }
    
    /**
     * Insert every element in the set of matched elements before the target.
     * @param pQryTag $target Tag element container
     * 
     @return \pQryTag The object reference
     */
    public function insertBefore(pQryTag $target) {
        $target->before($this);
        return $this;
    }
    
    /**
     * Check the current matched set of elements against a selector, Tag element or function
     * and return true if at least one of these elements matches the given arguments.
     * @param mixed $selectorOrOther
     *      selector - A string containing a selector expression to match elements against.
     *      function(Tag) - A function used as a test for the set of elements. 
     *      element - An Tag element to match the current set of elements against.
     * 
     * @return boolean
     */
    public function is($selectorOrOther) {
        if ($selectorOrOther instanceof pQryTag)
            return $this == $selectorOrOther;
        else if (is_callable($selectorOrOther))
            return $selectorOrOther($this);
        else
            return (bool)count(pQryCore::search($this, $selectorOrOther, array('deep'=>false)));
    }
    
    /**
     * Reduce the set of matched elements to the final one in the set.
     * 
     * @return mixed \Tag or empty \Tags when not found index
     */
    public function last() {
        return $this->eq($this->length());
    }
    
    /**
     * The number of elements in the Tag object.
     * 
     * @return int
     */
    public function length() {
        return count($this->children()->toArray());
    }
    
    /**
     * Verify if the object satisfy the rules
     * @param array $rules rules list
     *      id - Id to filter without #
     *      tag - Tag name to filter or * for include all
     *      class - Class name to filter without .
     *      attr - List of attributes, operator and value to filter. Operator and values are optionally
     *          operators:
     *              |= Selects elements that have the specified attribute with a value either equal to a given string or starting with that string followed by a hyphen (-)
     *              *= Selects elements that have the specified attribute with a value containing the a given substring.
     *              ~= Selects elements that have the specified attribute with a value containing a given word, delimited by spaces.
     *              $= Selects elements that have the specified attribute with a value ending exactly with a given string. The comparison is case sensitive.
     *               =  Selects elements that have the specified attribute with a value exactly equal to a certain value.
     *              != Select elements that either don't have the specified attribute, or do have the specified attribute but not with a certain value.
     *              ^= Selects elements that have the specified attribute with a value beginning exactly with a given string.
     *      pseudo - List of pseudo elements to match
     *          :button
     *          :checkbox
     *          :checked
     *          :empty
     *          :file
     *          :header
     *          :image
     *          :input
     *          :password
     *          :radio
     *          :reset
     *          :selected
     *          :submit
     *
     * @return boolean true if match, false otherwise
     */
    public function match($rules) {
        if (!empty($rules['tag']) && $rules['tag'] != $this->getTagName() && $rules['tag'] != '*')
            return false;
        if (!empty($rules['id']) && $this->id() != $rules['id'])
            return false;
        if (!empty($rules['class']) && !in_array($rules['class'], explode(' ', $this->attr('class'))))
            return false;
        if (!empty($rules['attr'])) {
            foreach ($rules['attr'] as $attr => $val) {
                if (is_numeric($attr)) $attr = $val;
                $vl = $this->attr($attr);
                if (!is_array($val) && $vl == '')
                    return false;
                else {
                    switch($val['op']) {
                        case '=':
                            if ($vl != $val['value']) return false;
                            break;
                        case '!=':
                            if ($vl == $val['value']) return false;
                            break;
                        case '*=':
                            if (strpos($vl, $val['value']) === false) return false;
                            break;
                        case '^=':
                            if (strpos($vl, $val['value']) !== 0) return false;
                            break;
                        case '$=':
                            if (substr($vl, -1 * strlen($val['value'])) !== $val['value']) return false;
                            break;
                        case '|=':
                            if ($vl != $val['value'] && strpos($vl, $val['value'] . '-') !== 0) return false;
                            break;
                        case '~=':
                            if (!in_array($val['value'], explode(' ', $vl))) return false;
                            break;
                    }
                }
            }
        }
        if (!empty($rules['pseudo'])) {
            foreach ($rules['pseudo'] as $rule) {
                switch ($rule) {
                    case ':button': case 'button':
                        if (!in_array($this->getTagName(), array('button', 'input')))
                           return false;
                        if ($this->getTagName() == 'input' && !in_array($this->attr('type'), array('button','submit','reset')))
                           return false;
                        break;
                    case ':checkbox': case 'checkbox': 
                    case ':file': case 'file':
                    case ':image': case 'image':
                    case ':password': case 'password':
                    case ':radio': case 'radio':    
                        if ($rule[0] == ':') $rule = substr($rule,1);
                        $rule = array('tag'=>'input', 'attr'=>array('type'=>array('op'=>'=', 'value'=>$rule)));
                        if (!$this->match($rule)) return false;
                        break;
                    case ':checked': case 'checked':
                        if ($this->getTagName() != 'input') return false;
                        if (!in_array($this->attr('type'),array('checkbox','radio'))) return false;
                        if (!$this->prop('checked') && !$this->prop('selected')) return false;
                        break; 
                   case ':disabled': case 'disabled':
                       if (!$this->prop('disabled')) return false;
                       break;
                   case ':empty': case 'empty':
                       if (count($this->content)) return false;
                       break;
                   case ':enabled': case 'enabled':
                       if ($this->prop('disabled')) return false;
                       break;        
                   case ':header': case 'header':
                       if (!in_array($this->getTagName(), array('h1','h2','h3','h4','h5','h6')))
                               return false;
                       break;
                   case ':input': case 'input':
                       if (!in_array($this->getTagName(), array('input', 'select', 'textarea')))
                               return false;
                       break;
                   case ':reset': case 'reset':
                   case ':submit': case 'submit':
                        if ($rule[0] == ':') $rule = substr($rule,1);
                        if (!in_array($this->getTagName(), array('button', 'input')))
                           return false;
                        if ($this->attr('type') != $rule)
                           return false;
                        break;
                    case ':selected': case 'selected':
                        if ($this->getTagName() != 'option') return false;
                        if (!$this->prop('selected')    ) return false;
                        break; 
                }
            }
        }
        return true;
    }
    
    /**
     * Get the immediately following sibling of each element in the set of matched elements.
     * If a selector is provided, it retrieves the next sibling only if it matches that selector.
     * @param string $selector A string containing a selector expression to match elements against.
     * 
     * @return mixed \Tag or empty \Tags when not found next
     */
    public function next($selector=null) {
        if (is_null($selector)) {
            return $this->eq($this->index() + 1);
        }
        else {
            $list = $this->parent()->children()->toArray();
            $ret = pQryCore::search(array_slice($list, $this->index()+1), $selector, array('deep'=>false, 'max'=>1));
            if (count($ret))
                return $ret[0];
            else
                return new pQryObj(array(), $this);
        }
    }
    
    /**
     * Get all following siblings of each element in the set of matched elements, optionally filtered by a selector.
     * @param string $selector A string containing a selector expression to match elements against.
     * 
     * @return \pQryObj List of all next
     */
    public function nextAll($selector=null) {
        return $this->nextUntil(null, $selector);
    }
    
    /**
     * Get all following siblings of each element up to but not including the element matched by the selector or Tag object passed.
     * @param mixed $elementOrSelector
     *      A string containing a selector expression to indicate where to stop matching following sibling elements.
     *      A Tag object indicating where to stop matching following sibling elements.
     * @param type $filter A string containing a selector expression to match elements against.
     * 
     * @return \pQryObj List of all next
     */
    public function nextUntil($elementOrSelector, $filter=null) {
        $list = $this->parent()->children()->toArray();
        $start = $this->index()+1;
        if (empty($elementOrSelector))
            $end = count($list) - $start;
        else if($elementOrSelector instanceof pQryTag)
            $end = $elementOrSelector->index() - $start;
        else if(is_string($elementOrSelector))
            return $this->nextUntil($this->next($elementOrSelector), $filter);
        else
            $end = 0;
        
        if (is_null($filter)) {
            return new pQryObj(array_slice($list, $start, $end), $this);
        }
        else {
            return new pQryObj(pQryCore::search(array_slice($list, $start, $end), $filter, array('deep'=>false)), $this);
        }
    }
    
    /**
     * Remove elements from the set of matched elements.
     * @param mixed $selectOrElement
     *      selector - A string containing a selector expression to match elements against.
     *      elements - Array with one or more \Tag elements to remove from the matched set.
     *      function(Tag) - A function used as a test for each element in the set.
     *      \Tag or \Tags - Elements to be filtered
     * 
     * @return \pQryTag The object reference
     */
    public function not($selectOrElement) {
        if ($selectOrElement instanceof pQryTag)
            $filter = array($selectOrElement);
        else if($selectOrElement instanceof pQryObj)
            $filter = $selectOrElement->toArray();
        else if (is_callable($selectOrElement)) {
            $filter = array();
            foreach ($this->children()->toArray() as $content) {
                if ($selectOrElement($content)) $filter[] = $content;
            }
        }
        else if (is_string($selectOrElement))
            $filter = pQryCore::search($this->children(), $selectOrElement, array('deep'=>false));
        else if (is_array($selectOrElement))
            $filter = $selectOrElement;
        else
            $filter = array();
        
        $this->content = array_diff($this->content, $filter);
        return $this;
    }
    
    /**
     * Get the parent of each element in the current set of matched elements, optionally filtered by a selector
     * @param string $selector A string containing a selector expression to match elements against.
     * 
     * @return mixed \Tag or empty \Tags when not found next
     */
    public function parent($selector=null) {
        if (empty($this->parent) && is_null($selector)) {
            if ($this instanceof pQryEmpty)
                return new pQryObj($this);
            else {
                $obj = new pQryEmpty();
                $obj->insertIn($this, 0);
            }
        }
        if (is_null($selector))
            return $this->parent;
        else {
            $ret = pQryCore::search($this->parent, $selector, array('deep'=>false));
            if (count($ret)) return $ret;
            else return new pQryObj(array(), $this);
        }
    }
    
    /**
     * Get the ancestors of each element in the current set of matched elements, optionally filtered by a selector.
     * @param string $selector A string containing a selector expression to match elements against.
     * 
     * @return \pQryObj with all parents
     */
    public function parents($selector=null) {
        return $this->parentUntil(null, $selector);
    }
    
    /**
     * Get the ancestors of each parent element in the current set of matched elements, up to but not including the element matched by the selector or Tag object.
     * @param mixed $elementOrSelector
     *      A string containing a selector expression to indicate where to stop matching following parent elements.
     *      A Tag object indicating where to stop matching following parent elements.
     * @param type $filter A string containing a selector expression to match elements against.
     * 
     * @return \pQryObj List of all next
     */
    public function parentsUntil($elementOrSelector, $filter=null) {
        if (is_string($elementOrSelector)) {
            $list = pQryCore::search($this, $elementOrSelector, array('dir'=>'up', 'max'=>1));
            if (count($list))
                $element = $list[0];
            else
                $element = null;
        }
        else $element = $elementOrSelector;
        
        if (empty($filter))
            $filter = "*";
        return new pQryObj(pQryCore::search($this->parent(), $filter, array('dir'=>'up', 'until'=>$element)), $this);
    }
    
    /**
     * Insert content, specified by the parameter, to the beginning of each element in the set of matched elements.
     * @param mixed $content Accept one or more additional content
     *      HTML string, 
     *      Tag or Tags object, 
     *      function function(Tag) to append in this element.
     *      Array of all types
     * 
     * @return \pQryTag - The object reference
     */
    public function prepend($content) {
        return $this->insertIn($content, 0);
    }
    
    /**
     * Insert every element in the set of matched elements to the begin of the target.
     * @param mixed $target Tag or Tags object
     * 
     * @return \pQryTag - The object reference
     */
    public function prependTo($target) {
        $target->prepend($this);
    }
    
    /**
     * Get the immediately preceding sibling of each element in the set of matched elements.
     * If a selector is provided, it retrieves the prev sibling only if it matches that selector.
     * @param string $selector A string containing a selector expression to match elements against.
     * 
     * @return mixed \Tag or empty \Tags when not found prev
     */
    public function prev($selector=null) {
        if (is_null($selector)) {
            return $this->eq($this->index() - 1);
        }
        else {
            $list = $this->parent()->children()->toArray();
            $ret = pQryCore::search(array_slice($list, 0, $this->index()-1), $selector, array('deep'=>false, 'max'=>1));
            if (count($ret))
                return $ret[0];
            else
                return new pQryObj(array(), $this);
        }
    }
    
    /**
     * Get all preceding siblings of each element in the set of matched elements, optionally filtered by a selector.
     * @param string $selector A string containing a selector expression to match elements against.
     * 
     * @return \pQryObj List of all prev
     */
    public function prevAll($selector=null) {
        return $this->prevUntil(null, $selector);
    }
    
    /**
     * Get all preceding siblings of each element up to but not including the element matched by the selector or \Tag object.
     * @param mixed $elementOrSelector
     *      A string containing a selector expression to indicate where to stop matching following sibling elements.
     *      A Tag object indicating where to stop matching following sibling elements.
     * @param type $filter A string containing a selector expression to match elements against.
     * 
     * @return \pQryObj List of all prev
     */
    public function prevUntil($elementOrSelector, $filter=null) {
        $list = $this->parent()->children()->toArray();        
        if (empty($elementOrSelector))
            $end = count($list);
        else if($elementOrSelector instanceof pQryTag)
            $end = $elementOrSelector->index();
        else if(is_string($elementOrSelector))
            return $this->prevUntil($this->prev($elementOrSelector), $filter);
        else
            $end = 0;
        
        if (is_null($filter)) {
            return new pQryObj(array_slice($list, 0, $end), $this);
        }
        else {
            return new pQryObj(pQryCore::search(array_slice($list, 0, $end), $filter, array('deep'=>false)), $this);
        }
    }
    
    /**
     * Get the value of a property for the first element in the set of matched elements.
     * Set one or more properties for the set of matched elements.
     * @param string $propName Nome do atributo/propriedade
     *      propertyName The name of the property to set.
     *      A map of property-value pairs to set.
     * @param boolean $value 
     *      string - A value to set for the property.
     *      function(\Tag, propertyName) - A function returning the value to set.
     * 
     *  @return mixed In set return a \Tag object reference, in get return boolean value of property
     */
    public function prop($propName, $value=null) {
        if (is_null($value)) {
            // Metodo GET
            return strtolower($this->attr($propName)) == strtolower($propName);
        }
        else {
            // Metodo SET
            if ($value) {
                $this->attr($propName, $propName);
            }
            else {
                $this->removeAttr($propName);
            }
            return $this;
        }
    }
    
    /**
     * Remove the set of matched elements from the DOM.
     * @param mixed $contentOrSelector
     *      content A \Tag or \Tags object
     *      selector A selector expression that filters the set of matched elements to be removed.
     *      function(\Tag) The function define if remove or not
     * 
     * @return \pQryTag - The object reference
     */
    public function remove($contentOrSelector) {
        if (is_callable($contentOrSelector)) {
            $contents = array();
            foreach ($this->children()->toArray() as $content) {
                if ($contentOrSelector($content))
                    $contents[] = $content;
            }
        }
        else if (is_string($contentOrSelector))
            $contents = Selector($this->content, $contentOrSelector, array('deep'=>false));
        else if ($contentOrSelector instanceof pQryTag)
            $contents = array($contentOrSelector);
        else if ($contentOrSelector instanceof pQryObj)
            $contents = $contentOrSelector->toArray();
        else if (is_array($contentOrSelector))
            $contents = $contentOrSelector;
        else
            $contents = array();
        
        foreach ($contents as $content) {
            if (in_array($content, $this->content)) {
                $this->content = array_diff($this->content, array($content));
                $content->setParent(null);
            }
        }
        return $this;
    }
    
    /**
     * Remove an attribute from each element in the set of matched elements.
     * @param string $attrName
     *      name A string naming the piece of data to delete.
     *      list An array or space-separated string naming the pieces of attributes to delete.
     * 
     * @return \pQryTag - The object reference
     */
    public function removeAttr($attrName) {
        if (!is_array($attrName))
            $attrName = explode(' ', $attrName);
        
        foreach ($attrName as $name) {
            if (isset($this->attributes[$name]))
                unset($this->attributes[$name]);
        }
        
        return $this;
    }
    
    /**
     * Remove a single class, multiple classes, or all classes from each element in the set of matched elements.
     * @param string $classOrFunction 
     *      className One or more space-separated classes to be removed from the class attribute of each matched element.
     *      function(Tag, classActual) A function returning one or more space-separated class names to be removed. 
     * 
     * @return \pQryTag - The object reference
     */
    public function removeClass($classOrFunction) {
        $newClasses = $this->attr('class');
        if (is_callable($classOrFunction))
            $classOrFunction = $classOrFunction($this, $newClasses);

        $classes = explode(' ', $classOrFunction);
        foreach ($classes as $cls)
            $newClasses = str_replace($cls, '', $newClasses);
        
        $this->attr('class', $newClasses);
        return $this;
    }
    
    /**
     * Remove a previously-stored piece of data.
     * @param mixed $dataName
     *      name A string naming the piece of data to delete.
     *      list An array or space-separated string naming the pieces of data to delete.
     * 
     * @return \pQryTag - The object reference
     */
    public function removeData($dataName) {
        if (!is_array($dataName))
            $dataName = explode(' ', $dataName);
        
        foreach ($dataName as $data) {
            $this->removeAttr('data-' . $data);
        }
        
        return $this;
    }
    
    /**
     * Remove a property for the set of matched elements.
     * @param mixed $propName
     *      name A string naming the piece of data to delete.
     *      list An array or space-separated string naming the pieces of data to delete.
     * 
     * @return \pQryTag - The object reference
     */
    public function removeProp($propName) {
        return $this->removeAttr($propName);
    }
        
    /**
     * Get the siblings of each element in the set of matched elements, optionally filtered by a selector.
     * @param string $selector A string containing a selector expression to match elements against.
     * 
     * @return \pQryObj
     */
    public function siblings($selector=null) {
        $list = pQryCore::search($this->parent()->children(), $selector, array('deep'=>false));
        return new pQryObj(array_diff($list, array($this)), $this);
    }
    
    /**
     * Return the number of elements in the jQuery object.
     * The .size() method is functionally equivalent to the .length()
     * 
     * @return int
     */
    public function size() {
        return $this->length();
    }
    
    /**
     * Get the combined text contents of each element in the set of matched elements, including their descendants.
     * Set the content of each element in the set of matched elements to the specified text.
     * @param mixed $content 
     *          textString A string of text to set as the content of each matched element.
     *          function(Tag, text) A function returning the text content to set. 
     * 
     * @return mixed In set return a \Tag object reference, in get return string value of atribute
     */
    public function text($content=null) {
        if (is_null($content)) {
            // Metodo GET
            return strip_tags($this->html());
        }
        else {
            // Metodo SET
            $this->emptyContent();
            if (is_callable($content))
                $content = $content($this, $this->text());
            $this->content[] = htmlspecialchars($content);
            return $this;
        }
    }
    
    /**
     * Retrieve all the DOM elements contained in the jQuery set, as an array.
     * 
     * @return array
     */
    public function toArray() {
        return $this->children()->toArray();
    }
    
    /**
     * Convert all elements to html
     * 
     * @return string
     */
    public function toString() {
        $tagname = strtolower($this->getTagName());
        $html = '';
        if ($tagname) {
            $html = "<{$tagname}";
        
            // Attributes
            if (count($this->attributes)) {
                $attrs = array();
                foreach ($this->attributes as $name => $value) {
                    $attrs[] = $name . '="' . $this->attr($name) . '"';
                }
                $html .= ' ' . implode(' ', $attrs);
            }

            $html .= '>';
        }
        if ($this->hasEndtag()) {
            // Content
            foreach ($this->content as $content) {
                if ($content instanceof pQryTag)
                    $html .= $content->toString();
                else
                    $html .= $content;
            }
            
            // EndTag
            if ($tagname)
                $html .= "</{$tagname}>";
        }

        return $html;
    }
    
    /**
     * Get the current value of the first element in the set of matched elements.
     * Set the value of each element in the set of matched elements.
     * @param mixed $value
     *      value A string of text or an array of strings corresponding to the value of each matched element to set as selected/checked.
     *      function(Tag) A function returning the value to set. 
     * 
     * @return mixed In set return a \Tag object reference, in get return string value of atribute
     */
    public function val($value=null) {
        if (is_null($value)) {
            return $this->attr('value');
        }
        else {
            $this->attr('value', $value);
        }
        return $this;
    }
}
