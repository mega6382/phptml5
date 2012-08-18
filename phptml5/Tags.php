<?php
/**
 * Representa uma seleção de elementos (zero ou mais)
 * Possui comportamento semelhante ao Tag aplicando os metodos a todos os elementos da seleção
 */
class Tags {
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
        return count($this->elements);
    }
}
