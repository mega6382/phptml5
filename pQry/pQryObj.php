<?php
/**
 * This is the result of some selection
 * Represents zero or more Tag elements and apply the action for all
 */
class pQryObj implements IteratorAggregate, arrayaccess, Countable {
    /**
     * Owner/Creator of Tags - null when created by user
     * @var pQryTag or Tags element that created this Tags 
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
        } else if($listOrOwner instanceof pQryTag) {
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
     * @return \pQryObj Reference to object Tags
     */
    protected function merge($tagsOrVector) {
        if (is_array($tagsOrVector)) {
            foreach ($tagsOrVector as $obj) {
                if ($obj instanceof pQryObj) {
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
     * @return \pQryObj
     */
    public function andSelf() {
        if ($this->orign instanceof pQryObj)
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
        $newTags = new pQryObj(array(), $this);
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
            if ($data instanceof pQryTag) break;
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
     * @return \pQryObj
     */
    public function end() {
        if (is_null($this->orign))
            return new pQryObj();
        return $this->orign;
    }
    
    /**
     * @see \Tag::eq
     */
    public function eq($index) {
        if ($index < 0)
            $index = count($this->elements) + $index;
        if ($index < 0 || $index >= count($this->elements))
            return new pQryObj(array(), $this);
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
            $this->elements = pQryCore::select($this->elements, $selectorOrFunction, array('deep'=>false));
        }
        return $this;
    }
    
    /**
     * @see \Tag::find
     */
    public function find($selector) {
        $newTags = new pQryObj(array(), $this);
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
        if ($selectorOrTag instanceof pQryTag) {
            foreach ($this->elements as $content) {
                if ($content->contains($selectorOrTag) || $content == $selectorOrTag)
                    $list[] = $content;
            }
        }
        else {
            foreach ($this->elements as $content) {
                if (count(pQryCore::select($content, $selectorOrTag)))
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
        foreach ($this->elements as $i => $elem) $elem->id($value . '_' . $i);
        return $this;
    }
    
    /**
     * @see \Tag::index
     */
    public function index($selectorOrTag) {
        if (is_string($selectorOrTag)) {
            $list = pQryCore::select($this->elements, $selectorOrTag, array('deep'=>false, 'max'=>1));
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
    public function insertAfter(pQryTag $target) {
        foreach ($this->elements as $elem) $elem->insertAfter($target);
        return $this;
    }
    
    /**
     * @see \Tag::insertBefore
     */
    public function insertBefore(pQryTag $target) {
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
    
    /**
     * @see \Tag::last
     */
    public function last() {
        return $this->eq($this->length());
    }
    
    /**
     * @see \Tag::last
     */
    public function length() {
        return count($this->elements);
    }
    
    /**
     * @see \Tag::next
     */
    public function next($selector=null) {
        if (is_null($selector)) {
            return $this->eq($this->current + 1);
        }
        else if ($selector instanceof pQryTag) {
            if (in_array($selector, $this->elements))
                return $selector->next();
            return new pQryObj(array(), $this);
        }
        else {
            $ret = pQryCore::select(array_slice($this->elements, $this->current+1), $selector, array('deep'=>false, 'max'=>1));
            if (count($ret))
                return $ret[0];
            else
                return new pQryObj(array(), $this);
        }
    }
    
    /**
     * @see \Tag::nextAll
     */
    public function nextAll($selector=null) {
         return $this->nextUntil(null, $selector);
    }
    
    /**
     * @see \Tag::nextUntil
     */
    public function nextUntil($elementOrSelector, $filter=null) {
        if (empty($elementOrSelector))
            $end = count($this->elements) - ($this->current+1);
        else if($elementOrSelector instanceof pQryTag)
            $end = $elementOrSelector->index() - $start;
        else if(is_string($elementOrSelector))
            return $this->nextUntil($this->next($elementOrSelector), $filter);
        else
            $end = 0;
        if (is_null($filter)) {
            return new pQryObj(array_slice($this->elements, $this->current+1, $end), $this);
        }
        else if ($filter instanceof pQryTag) {
            if (in_array($filter, $this->elements))
                return $filter->nextUntil($elementOrSelector);
            return new pQryObj(array(), $this);
        }
        else {
            $list = pQryCore::select(array_slice($this->elements, $this->current+1, $end), $selector, array('deep'=>false));
            return new pQryObj($list, $this);
        }
    }
    
    /**
     * @see \Tag::not
     */
    public function not($selectOrElement) {
        if ($selectOrElement instanceof pQryTag)
            $filter = array($selectOrElement);
        else if($selectOrElement instanceof pQryObj)
            $filter = $selectOrElement->toArray();
        else if (is_callable($selectOrElement)) {
            $filter = array();
            foreach ($this->elements as $content) {
                if ($selectOrElement($content)) $filter[] = $content;
            }
        }
        else if (is_string($selectOrElement))
            $filter = pQryCore::select($this->elements, $selectOrElement, array('deep'=>false));
        else if (is_array($selectOrElement))
            $filter = $selectOrElement;
        else
            $filter = array();
        
        $this->elements = array_diff($this->elements, $filter);
        return $this;
    }
    
    /**
     * @see \Tag::parent
     */
    public function parent($selector=null) {
        $list = array();
        foreach ($this->elements as $ele) {
            $ret = $ele->parent($selector);
            if ($ret instanceof pQryTag)
                $list[] = $ret;
        }
        return new pQryObj($list, $this);
    }
    
    /**
     * @see \Tag::parents
     */
    public function parents($selector=null) {
        return $this->parentsUntil(null, $selector);
    }
    
    /**
     * @see \Tag::parentsUntil
     */
    public function parentsUntil($elementOrSelector, $filter=null) {
        if (is_string($elementOrSelector)) {
            $list = pQryCore::select($this, $elementOrSelector, array('dir'=>'up', 'max'=>1));
            if (count($list))
                $element = $list[0];
            else
                $element = null;
        }
        else $element = $elementOrSelector;
        
        if (empty($filter))
            $filter = "*";
        return new pQryObj(pQryCore::select($this->parent(), $filter, array('dir'=>'up', 'until'=>$element)), $this);
    }
    
    /**
     * @see \Tag::prepend
     */
    public function prepend($contents=null) {
        if (!is_array($contents) || !count($contents))
            $contents = func_get_args();
        foreach ($this->elements as $elem) { $elem->prepend($contents); }
        return $this;
    }
    
    /**
     * @see \Tag::prependTo
     */
    public function prependTo($target) {
        foreach ($this->elements as $elem) { $target->prependTo($elem); }
        return $this;
    }
    
    /**
     * @see \Tag::prev
     */
    public function prev($selector=null) {
        if (is_null($selector)) {
            return $this->eq($this->current - 1);
        }
        else if ($selector instanceof pQryTag) {
            if (in_array($selector, $this->elements))
                return $selector->prev();
            return new pQryObj(array(), $this);
        }
        else {
            $ret = pQryCore::select(array_slice($this->elements, 0, $this->current), $selector, array('deep'=>false, 'max'=>1));
            if (count($ret))
                return $ret[0];
            else
                return new pQryObj(array(), $this);
        }
    }
    
    /**
     * @see \Tag::prevAll
     */
    public function prevAll($selector=null) {
         return $this->prevUntil(null, $selector);
    }
    
    /**
     * @see \Tag::prevUntil
     */
    public function prevUntil($elementOrSelector, $filter=null) {
        if (empty($elementOrSelector))
            $end = $this->current;
        else if($elementOrSelector instanceof pQryTag)
            $end = $elementOrSelector->index();
        else if(is_string($elementOrSelector))
            return $this->prevUntil($this->prev($elementOrSelector), $filter);
        else
            $end = 0;
        if (is_null($filter)) {
            return new pQryObj(array_slice($this->elements, 0, $end), $this);
        }
        else if ($filter instanceof pQryTag) {
            if (in_array($filter, $this->elements))
                return $filter->prevUntil($elementOrSelector);
            return new pQryObj(array(), $this);
        }
        else {
            $list = pQryCore::select(array_slice($this->elements, 0, $end), $selector, array('deep'=>false));
            return new pQryObj($list, $this);
        }
    }
    
    /**
     * @see \Tag::prop
     */
    public function prop($propName, $value=null) {
        //GET
        if (is_string($propName) && is_null($value)) {
            if (count($this->elements))
                return $this->elements[0]->prop($propName);
            else
                return false;
        }
        // SET
        foreach ($this->elements as $elem) $elem->prop($propName, $value);
        return $this;
    }
    
    /**
     * @see \Tag::remove
     */
    public function remove($contentOrSelector) {
        foreach ($this->elements as $elem) $elem->remove($contentOrSelector);
        return $this;
    }
    
    /**
     * @see \Tag::removeAttr
     */
    public function removeAttr($attrName) {
        foreach ($this->elements as $elem) $elem->removeAttr($attrName);
        return $this;
    }
    
    /**
     * @see \Tag::removeClass
     */
    public function removeClass($classOrFunction) {
        foreach ($this->elements as $elem) $elem->removeAttr($classOrFunction);
        return $this;
    }
    
    /**
     * @see \Tag::removeData
     */
    public function removeData($dataName) {
        foreach ($this->elements as $elem) $elem->removeData($dataName);
        return $this;
    }
    
    /**
     * @see \Tag::removeProp
     */
    public function removeProp($propName) {
        foreach ($this->elements as $elem) $elem->removeProp($propName);
        return $this;
    }
        
    /**
     * @see \Tag::siblings
     */
    public function siblings($selector=null) {
        $newTags = new pQryObj(array(), $this);
        foreach ($this->elements as $elem) { $newTags->merge($elem->siblings($selector)); }
        return $newTags;
    }
    
    /**
     * Same as length
     */
    public function size() {
        return $this->length();
    }

    /**
     * @see \Tag::text
     */
    public function text($content=null) {
        //GET
        if (is_null($content)) {
            if(count($this->elements))
                return $this->elements[0]->text();
            else
                return '';
        }
        // SET
        foreach ($this->elements as $elem) $elem->text($content);
        return $this;
    }
    
    /**
     * @see \Tag::toArray
     */
    public function toArray() {
        return $this->elements;
    }
    
    public function toString() {
        $html = '';
        foreach ($this->elements as $elem)
            $html .= $elem->toString();
        return $html;
    }
    
    /**
     * @see \Tag::val
     */
    public function val($value=null) {
        //GET
        if (is_null($value)) {
            if(count($this->elements))
                return $this->elements[0]->val();
            else
                return null;
        }
        // SET
        foreach ($this->elements as $elem) $elem->val($value);
        return $this;
    }
}
