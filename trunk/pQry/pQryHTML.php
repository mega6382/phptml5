<?php

/**
 * Classe Genérica para criar uma classe com o nome informado pelo usuário
 */
class pQryHTML extends pQryTag {
    private $name;
    private $endTag;
    
    /**
     * Cria uma Tag genérica
     * 
     * @param string $tag Nome da tag que representa o elemento
     * @param boolean $endTag Valor lógico que define se a tag possui fechamento (TRUE) ou não (FALSE)
     */
    public function __construct($tag, $endTag=true){
        $this->name = $tag;
        $this->endTag = $endTag;
    }

    protected function getAttributeList() {
        return array();
    }

    protected function getTagName() {
        return $this->name;
    }

    protected function hasEndtag() {
        return $this->endTag;
    }
}