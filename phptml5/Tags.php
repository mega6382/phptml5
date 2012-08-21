<?php
/**
 * Representa uma seleção de elementos (zero ou mais)
 * Possui comportamento semelhante ao Tag aplicando os metodos a todos os elementos da seleção
 */
class Tags extends Tag implements IteratorAggregate, arrayaccess, Countable {
    private $elements = array();
    
    public function __construct($list=null) {
        if (is_array($list))
            $this->elements = $list;
    }
    
    /**
     * Retorna a quantidade de elementos presente na lista de elementos
     * @return int
     */
    public function size() {
        return count($this);
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
    
    // Override all methods
    
    public function toString() {
        $html = '';
        foreach ($this->elements as $elem)
            $html .= $elem->toString();
        return $html;
    }
}
