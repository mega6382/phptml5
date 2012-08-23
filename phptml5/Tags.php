<?php
/**
 * This is the result of some selection
 * Represents zero or more Tag elements and apply the action for all
 */
class Tags implements IteratorAggregate, arrayaccess, Countable {
    /**
     * Owner/Creator of Tags - null when created by user
     * @var Tag or Tags element that created this Tags 
     */
    protected $orign = null;
    
    /**
     * List of elements selected
     * @var array Array of Tag elements that is containg selector
     */
    protected $elements = array();
    
    /**
     * Cursor appointing the current item in the stack
     * @var int 
     */
    protected $current = 0;
    
    public function __construct($listOrOwner=null, $owner=null) {
        if (is_array($listOrOwner)) {
            $this->elements = $listOrOwner;
            $this->orign = $owner;
        } else if($listOrOwner instanceof Tag) {
            $this->orign = $listOrOwner;
        }
    }
 
    protected function getAttributeList() {
        return array();
    }

    protected function getTagName() {
        return '';
    }

    protected function hasEndtag() {
        return true;
    }

    public function count() {
        return count($this->elements);
    }

    public function offsetExists($offset) {
        return (is_numeric($offset) && $offset >= 0 && $offset < count($this));
    }

    public function offsetGet($offset) {
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        die('TODO - Dont possible yet');
        //http://www.php.net/manual/en/class.arrayaccess.php
    }

    public function offsetUnset($offset) {
        die('TODO - Dont possible yet');
    }

    public function getIterator() {
        return $this->elements;
    }
    
    /**
     * Merge with Tags or array
     * @param mixed $tagsOrVector Tags with elements or array of elements
     * 
     * @return \Tags Reference to object Tags
     */
    protected function merge($tagsOrVector) {
        if (is_array($tagsOrVector)) {
            foreach ($tagsOrVector as $obj) {
                if ($obj instanceof Tags) {
                    $this->merge($obj);
                }
                else {
                    if (!in_array($obj, $this->elements))
                        $this->elements[] = $obj;
                }
            }
        }
        else {
            foreach ($tagsOrVector->elements as $obj) {
                if (!in_array($obj, $this->elements))
                    $this->elements[] = $obj;
            }
        }
        return $this;
    }
    
    // Override all methods
    /**
     * @see \Tag::add
     */
    public function add($content) {
        foreach ($this->elements as $elem) { $elem->add($content); }
        return $this;
    }
    
    /**
     * @see \Tag::addClass
     */
    public function addClass($classNameOrFunction) {
        foreach ($this->elements as $elem) { $elem->addClass($classNameOrFunction); }
        return $this;
    }
    
    /**
     * @see \Tag::after
     */
    public function after($contents=null) {
        if (!is_array($contents) || !count($contents))
            $contents = func_get_args();
        foreach ($this->elements as $elem) { $elem->after($contents); }
        return $this;
    }
    
    /**
     * Add the previous set of elements on the stack to the current set.
     * @return \Tags
     */
    public function andSelf() {
        if ($this->orign instanceof Tags)
            $list = $this->orign->elements;
        else
            $list = $this->orign;
        $this->elements = array_merge($list, $this->elements);
        $this->current = 0;
        return $this;
    }
    
    
    /**
     * @see \Tag::append
     */
    public function append($contents=null) {
        if (!is_array($contents) || !count($contents))
            $contents = func_get_args();
        foreach ($this->elements as $elem) { $elem->append($contents); }
        return $this;
    }
    
    /**
     * @see \Tag::appendTo
     */
    public function appendTo($target) {
        foreach ($this->elements as $elem) { $target->appendTo($elem); }
        return $this;
    }
    
    /**
     * @see \Tag::attr
     */
    public function attr($nameOrList, $value=null) {
        //GET
        if (is_string($nameOrList) && is_null($value)) { 
            if (count($this->elements))
                return $this->elements[0]->attr($nameOrList);
            else
                return '';
        }
        // SET
        foreach ($this->elements as $elem) { $elem->attr($nameOrList, $value); }
        return $this;
    }
    
    /**
     * @see \Tag::before
     */
    public function before($contents=null) {
        if (!is_array($contents) || !count($contents))
            $contents = func_get_args();
        foreach ($this->elements as $elem) { $elem->before($contents); }
        return $this;
    }
    
    
    /**
     * @see \Tag::children
     */
    public function children($selector=null) {
        $newTags = new Tags(array(), $this);
        foreach ($this->elements as $elem) { $newTags->merge($elem->children($selector)); }
        return $newTags;
    }
    
    public function cloneThis($withAttr=true, $withContent=true) {
         $obj = clone $this;
         for ($i = 0; $i < count($this->contents); $i++) {
            $this->contents[$i] = $this->contents[$i]->cloneThis($withAttr, $withContent);
         }
         return $obj;
    }
    
    /**
     *  Create a deep copy of the set of matched elements.
     */
    public function __clone() {
        for ($i = 0; $i < count($this->contents); $i++) {
            $this->contents[$i] = $this->contents[$i]->cloneThis();
        }
    }
    
    /**
     * @see \Tag::closest
     */
    public function closest($selector=null) {
        foreach ($this->contents as $content) {
            $data = $content->closest($selector);
            if ($data instanceof Tag) break;
        }
        return $data;
    }
    
    /**
     * @see \Tag::contains
     */
    public function contains($contained) {
        foreach ($this->contents as $content) {
            if($content->contains($contained))
                return true;
        }
        return false;
    }
    
    /**
     * @see \Tag::contents
     */
    public function contents() {
         $list = array();
         foreach ($this->elements as $elem) {
             $aux = $elem->contents();
             foreach($aux as $obj) $list[] = $obj;
         }
         return $list;
    }
    
    /**
     * @see \Tag::css
     */
    public function css($styleOrList, $value=null) {
        //GET
        if (is_string($styleOrList) && is_null($value)) { 
            if(count($this->elements))  
                return $this->elements[0]->attr($styleOrList);
            else
                return '';
        }
        // SET
        foreach ($this->elements as $elem) $elem->css($styleOrList, $value);
        return $this;
    }
    
    /**
     * @see \Tag::data
     */
    public function data($nameOrList, $value=null) {
        //GET
        if (is_string($nameOrList) && is_null($value)) {
            if(count($this->elements))  
                return $this->elements[0]->data($nameOrList);
            else
                return '';
        }
        // SET
        foreach ($this->elements as $elem) $elem->data($nameOrList, $value);
        return $this;
    }
    
    /**
     * @see \Tag::each
     */
    public function each($function, $onlyTag=false) {
        if (is_callable($function)) {
            foreach ($this->elements as $i => $content) {
                if ($onlyTag && is_string($content)) continue;
                if ($function($i, $content) === false) break;
            }
        }
        return $this;
    }
    
    /**
     * @see \Tag::emptyAll
     */
    public function emptyAll() {
        foreach ($this->elements as $elem) $elem->emptyAll();
    }
    
    /**
     * @see \Tag::emptyAttr
     */
    public function emptyAttr($recursive=false) {
        foreach ($this->elements as $elem) $elem->emptyAttr($recursive);
    }
    
    /**
     * @see \Tag::emptyContent
     */
    public function emptyContent() {
        foreach ($this->elements as $elem) $elem->emptyContent();
    }
    
    /**
     * End the most recent filtering operation in the current chain and return the set of matched elements to its previous state.
     * @return \Tags
     */
    public function end() {
        if (is_null($this->orign))
            return new Tags();
        return $this->orign;
    }
    
    /**
     * @see \Tag::eq
     */
    public function eq($index) {
        if ($index < 0)
            $index = count($this->elements) + $index;
        if ($index < 0 || $index >= count($this->elements))
            return new Tags(array(), $this);
        return $this->elements[$index];
    }
        
    /**
     * @see \Tag::filter
     */
    public function filter($selectorOrFunction) {
        if (is_callable($selectorOrFunction)) {
            $this->elements = array_filter($this->elements, $selectorOrFunction);
        }
        else {
            $this->elements = Selector::run($this->elements, $selectorOrFunction, array('deep'=>false));
        }
        return $this;
    }
    
    /**
     * @see \Tag::find
     */
    public function find($selector) {
        $newTags = new Tags(array(), $this);
        foreach ($this->elements as $elem) { $newTags->merge($elem->find($selector)); }
        return $newTags;
    }
    
    /**
     * @see \Tag::first
     */
    public function first() {
        return $this->eq(0);
    }
    
    /**
     * @see \Tag::has
     */
    public function has($selectorOrTag) {
        $list = array();
        if ($selectorOrTag instanceof Tag) {
            foreach ($this->elements as $content) {
                if ($content->contains($selectorOrTag) || $content == $selectorOrTag)
                    $list[] = $content;
            }
        }
        else {
            foreach ($this->elements as $content) {
                if (count(Selector::run($content, $selectorOrTag)))
                    $list[] = $content;
            }
        }
        return $this;
    }
    
    /**
     * @see \Tag::hasClass
     */
    public function hasClass($classes) {
        foreach ($this->elements as $elem) {
            if ($elem->hasClass($classes))
                return true;
        }
        return false;
    }
    
    /**
     * @see \Tag::html
     */
    public function html($content=null) {
        //GET
        if (is_null($content)) {
            if(count($this->elements))
                return $this->elements[0]->html();
            else
                return '';
        }
        // SET
        foreach ($this->elements as $elem) $elem->html($content);
        return $this;
    }
    
    /**
     * @see \Tag::id
     */
    public function id($value=null) {
        //GET
        if (is_null($value)) {
            if(count($this->elements))
                return $this->elements[0]->id();
            else
                return '';
        }
        // SET
        foreach ($this->elements as $i => $elem) $elem->html($value . '_' . $value);
        return $this;
    }
    
    /**
     * @see \Tag::index
     */
    public function index($selectorOrTag) {
        if (is_string($selectorOrTag)) {
            $list = Selector::run($this->elements, $selectorOrTag, array('deep'=>false, 'max'=>1));
            if (count($list))
                $selectorOrTag = $list[0];
            else
                return -1;
        }
        $ret = array_search($selectorOrTag, $this->elements);  
        if ($ret === false)
            return -1;
        return $ret;
    }
    
    /**
     * @see \Tag::insertAfter
     */
    public function insertAfter(Tag $target) {
        foreach ($this->elements as $elem) $elem->insertAfter($target);
        return $this;
    }
    
    /**
     * @see \Tag::insertBefore
     */
    public function insertBefore(Tag $target) {
        foreach ($this->elements as $elem) $elem->insertBefore($target);
        return $this;
    }
    
    /**
     * @see \Tag::is
     */
    public function is($selectorOrOther) {
        foreach ($this->elements as $elem) {
            if ($elem->is($selectorOrOther)) return true;
        }
        return false;
    }
    
    public function size() {
        return count($this);
    }

    
    public function toString() {
        $html = '';
        foreach ($this->elements as $elem)
            $html .= $elem->toString();
        return $html;
    }
    
    public function toArray() {
        return $this->elements;
    }
}
