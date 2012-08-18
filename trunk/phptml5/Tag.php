<?php
/**
 * Possui classes abstratas fornecendo a base dos elementos htmls
 */

/**
 * Representa um elemento HTML abstrato.
 * Todos os elementos devem herdar esta classe
 *
 * @author Adriano_2012
 */
abstract class Tag {
    /**
     * Pai do elemento
     * @var Tag 
     */
    private $parent;
    
    /**
     * Vetor contendo os filhos do elemento.
     * O vetor pode ter elementos do tipo string e elementos do tipo Tag
     * 
     * @var array<string|Tag>
     */
    private $content = array();
    
    /**
     * Vetor associativo com os atributos (chave) e valores (valor)
     * 
     * @var array<string> Exemplo: array('nomeDoAtributo'=>'valor');
     */
    private $attributes = array();
    
    /* Metodos abstratos */
    
    /**
     * Retorna o nome da tag
     * 
     * @return string
     */
    protected abstract function getTagName();
    
    /**
     * Retorna se a tag possui tag de fechamento ou não
     * 
     * @return boolean - TRUE se possui tag de fechamento, FALSE caso contrario
     */
    protected abstract function hasEndtag();
    
    /**
     * Retorna a lista de atributos que é permitido ao elemento.
     * Não deve incluir os atributos comuns, apenas os especificos para o tipo
     * 
     * @return array<string> - Lista com os nomes dos atributos permitidos para o elemento
     */
    protected abstract function getAttributeList();
    
    /* Metodos base para o tratamento das tags */
    
    /**
     * Adiciona classes ao elemento
     * @param string $className Nome das classes separadas por espaço
     * 
     * @return Tag - Referencia para o objeto
     */
    public function addClass($className) {
        if (strlen($this->attr('class'))>0)
            $className = ' ' . $className;
        $this->attr('class', preg_replace('!\s+!', ' ', $this->attr('class') . $className));
        return $this;
    }
    
    /**
     * Adiciona conteudo de texto (html) ou novos objetos Tag ao final do elemento
     * @param string|Tag $content Quando string será convertido para Tag
     * 
     * @return Tag - Referencia para o elemento que acabou de adiconar um novo filho
     */
    public function append($content) {
        $contents = $this->parseInternal($content);
        foreach ($contents as $content) {
            if ($content instanceof Tag) {
                $content->setParent($this);
            }
            $this->content[] = $content;
        }
        return $this;
    }
    
    /**
     * Método sobrecarregado de GET ou SET para atributos, 
     *    SET - Parâmetro $nameOrList um vetor associativo ou parâmetro $value uma string
     *    GET - Parâmetro $value ausente e parâmetro $nameOrList deve ser uma string
     * @param string|array<string> $nameOrList Nome do atributo ou vetor associativo dos nomes com o respecitvo valor
     * @param string $value Valor do atributo
     * 
     * @return string|Tag Retorna o valor do atributo (get) ou a instancia da classe (set)
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
                $nameOrList = array($nameOrList => $value);
            }
            foreach ($nameOrList as $attr => $val) {
                $this->attributes[$this->cleanAttr($attr)] = $this->cleanText($val);
            }
            return $this;
        }
    }
    
    /**
     * Retorna uma lista (Tags) dos elementos filhos do elemento
     * 
     * @return Tags Lista com os filhos
     */
    public function children() {
        $list = array();
        foreach ($this->content as $content) {
            if ($content instanceof Tag)
                $list[] = $content;
        }
        return new Tags($list);
    }
    
    /**
     * Metodo para clonar os objetos
     * 
     * @return Tag Novo elemento clonado
     */
    public function cloneThis() {
        return clone $this;
    }
    
    /**
     * Metodo mágico utilizado para clonar objetos
     */
    public function __clone() {
        $this->parent = null;
        $contents = $this->content;
        $this->content = array();
        foreach ($contents as $content) {
            if ($content instanceof Tag)
                $this->append(clone $content);     
            else
                $this->append ($content);
        }
    }
    
    /**
     * Método sobrecarregado de GET ou SET para o css 
     *    SET - Parâmetro $styleOrList um vetor associativo ou parâmetro $value uma string
     *    GET - Parâmetro $value ausente e parâmetro $styleOrList deve ser uma string
     * @param string|array<string> $styleOrList Nome do estilo ou vetor associativo dos nomes com o respecitvo valor
     * @param string $value Valor do estilo
     * 
     * @return string|Tag Retorna o valor do estilo (get) ou a instancia da classe (set)
     * @todo Pensar o que fazer com estilos como url('location') ?
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
     * Método sobrecarregado de GET ou SET para dados do atributos, 
     *    SET - Parâmetro $nameOrList um vetor associativo ou parâmetro $value uma string
     *    GET - Parâmetro $value ausente e parâmetro $nameOrList deve ser uma string
     * @param string|array<string> $nameOrList Nome do atributo ou vetor associativo dos nomes com o respecitvo valor
     * @param string $value Valor do atributo
     * 
     * @return string|Tag Retorna o valor do atributo associado (get) ou a instancia da classe (set)
     */
    public function data($nameOrList, $value=null) {
        if (is_null($value) && is_string($nameOrList)) {
            // Metodo Get
            return $this->attr('data-' . $nameOrList);
        }
        else {
            // Metodo Set
            if (is_string($nameOrList)) {
                $nameOrList = array($nameOrList => $value);
            }
            foreach ($nameOrList as $attr => $val) {
                $this->attr('data-' . $attr, $val);
            }
            return $this;
        }
    }
    
    /**
     * Zera todo o conteudo do elemento
     * 
     * @return Tag Referencia para o elemento
     */
    public function emptyContent() {
        $this->content = array();
        return $this;
    }
    
    /**
     * Zera todos os atributos do elemento
     * 
     * @return Tag Referencia para o elemento
     */
    public function emptyAttr() {
        $this->attributes = array();
        return $this;
    }
    
    /**
     * Zera todos os atributos e conteudo
     * 
     * @return Tag Referencia para o elemento
     */
    public function emptyAll() {
        return $this->emptyContent()->emptyAttr();
    }
    
    /**
     * Verifica se uma ou mais classes estão presentes no elemento
     * @param string $classes Nome de uma ou mais classes (separadas por espaços)
     * 
     * @return boolean TRUE se a classe ou todas as classes estão presentes
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
     * Método sobrecarregado de GET ou SET para definir o conteudo do elemento 
     *    SET - Parâmetro $content diferente de null
     *    GET - Parâmetro $content igual a null
     * @param string|Tag $content Conteudo HTML ou Element Tag para substituir o conteudo atual
     * 
     * @return Tag|string Referencia para o elemento (set) ou texto html do conteudo do elemento (get)
     */
    public function html($content=null) {
        if (is_null($content)) {
            // Metodo GET
            $html = "";
            foreach ($this->content as $content) {
                if ($content instanceof Tag) {
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
     * Método sobrecarregado de GET ou SET para definir o attributo id para o elemento
     *    SET - Parâmetro $idValue diferente de null
     *    GET - Parâmetro $idValue igual a null
     * @param string $value Valor do id
     * 
     * @return Tag|string Referencia para o elemento (set) ou id do elemento (get)
     */
    public function id($idValue=null) {
        return $this->attr('id', $idValue);
    }
    
    /**
     * Retorna o pai do elemento em questão, se o elemento não possui pai retorna um objeto Tags
     * @return Tag|Tags - Referencia para o objeto
     */
    public function parent() {
        if (empty($this->parent))
            return new Tags();
        return $this->parent;
    }
    
    /**
     * Adiciona conteudo de texto (html) ou novos objetos Tag no inicio do elemento
     * @param string|Tag $content
     * 
     * @return Tag - Referencia para o elemento que acabou de adiconar um novo filho
     */
    public function prepend($content) {
        $contents = $this->parseInternal($content);
        foreach ($contents as $content) {
            if ($content instanceof Tag) {
                $content->setParent($this);
            }
            array_unshift($this->content, $content);
        }
        return $this;
    }
    
    /**
     * Método sobrecarregado de GET ou SET para definir propriedades para o elemento
     * Propriedade são atributos que possuem valor logico
     * Em html corresponde aos atributos que quando estão presentes a propriedade é verdadeira, caso contrario falso
     * Exemplo: checked, disabled, selected, etc
     *    SET - Parâmetro $value deve ser diferente de null 
     *    GET - Parâmetro $value ausente ou null
     * @param string $propName Nome do atributo/propriedade
     * @param boolean $value Valor lógico definindo se a propriedade está presente ou não
     * 
     * @return string|Tag Retorna o valor do atributo associado (get) ou a instancia da classe (set)
     */
    public function prop($propName, $value=null) {
        if (is_null($value)) {
            // Metodo GET
            return $this->attr($propName) == $propName;
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
     * Remove o elemento passado por parâmetro da lista de filhos
     * Se o elemento não for encontado, nada acontece
     * @param \Tag $content Objeto que deverá ser removido da lista de conteúdo
     * 
     * @return \Tag - Referencia para o elemento
     */
    public function remove($content) {
        if (in_array($content, $this->content)) {
            $this->content = array_diff($this->content, array($content));
            $content->setParent(null);
        }
        return $this;
    }
    
    /**
     * Remove um atributo eliminando assim seu valor
     * @param string $attrName Nome do atributo a ser removido
     * 
     * @return Tag - Referencia para o elemento
     */
    public function removeAttr($attrName) {
        if (isset($this->attributes[$attrName]))
            unset($this->attributes[$attrName]);
        return $this;
    }
    
    /**
     * Retira uma ou mais classes do elemento
     * @param string $className Nome das classes separadas por espaço
     * 
     * @return \Tag - Referencia para o objeto
     */
    public function removeClass($className) {
        $classes = explode(' ', $className);
        $newClasses = $this->attr('class');
        foreach ($classes as $cls) {
            $newClasses = str_replace($cls, '', $newClasses);
        }
        $this->attr('class', $newClasses);
        return $this;
    }
    
    /**
     * Remove um dado associado ao elemento
     * @param string $attrName Nome do atributo a ser removido
     * 
     * @return \Tag - Referencia para o elemento
     */
    public function removeData($attrName) {
        return $this->removeAttr('data-' . $attrName);
    }
    
    /**
     * Remove uma propriedade do elemento
     * @param string $propName Nome do atributo a ser removido
     * 
     * @return \Tag - Referencia para o elemento
     */
    public function removeProp($propName) {
        return $this->removeAttr($propName);
    }
    
    /**
     * Retorna uma lista (Tags) com os elementos que estão no mesmo nível no elemento e que possuem o mesmo pai
     * 
     * @return Tags Lista com os irmãos
     */
    public function siblings() {
        $list = array();
        //TODO transform Tags in iterable
        foreach ($this->parent()->children() as $content) {
            if ($content != $this)
                $list[] = $content;
        }
        new Tags($list);
    }
    
    /**
     * Retorna a quantidade elementos que o elemento possui
     * 
     * @return int
     */
    public function size() {
        return $this->children()->size();
    }
    
    /**
     * Método sobrecarregado de GET ou SET para definir o conteudo de texto do elemento 
     *    SET - Parâmetro $content diferente de null
     *    GET - Parâmetro $content igual a null
     * @param string $content Texto para substituir o conteudo atual
     * 
     * @return Tag|string Referencia para o elemento (set) ou texto do conteudo do elemento (get)
     */
    public function text($content=null) {
        if (is_null($content)) {
            // Metodo GET
            return strip_tags($this->html());
        }
        else {
            // Metodo SET
            $this->emptyContent();
            $this->content[] = htmlspecialchars($content);
            return $this;
        }
    }
    
    /**
     * Retorna o elemento e todo o seu conteudo renderizado no formato HTML
     * 
     * @return string String HTML do elemento renderizado
     */
    public function toString() {
        $tagname = strtolower($this->getTagName());
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
        
        if ($this->hasEndtag()) {
            // Content
            foreach ($this->content as $content) {
                if ($content instanceof Tag)
                    $html .= $content->toString();
                else
                    $html .= $content;
            }
            
            // EndTag
            $html .= "</{$tagname}>";
        }

        return $html;
    }
    
    /**
     * @see Tag::toString
     * @return string
     */
    public function __toString() {
        return $this->toString();
    }
    
    // val
    
    /* Metodos utilitarios */
    /**
     * Limpa um texto retornando sem tags html e sem espaços adicionais e transformando caracteres especiais em marcação html
     * 
     * @param string $text
     */
    protected function cleanText($text) {
        return trim(htmlspecialchars(strip_tags($text))); 
    }
    
    /**
     * Limpa um texto retornando sem caracteres especiais, tags html, sem espaços adicionais e minusculo 
     * @param string $text
     * 
     * @return string Novo texto para utilizar nos atributos e seus valores
     */
    protected function cleanAttr($text) {
        return preg_replace("/&#?[a-z0-9]{2,8};/i","",strtolower($this->cleanText($text)));
    }
    
    /**
     * Faz o parse do elemento se necessário, quando o texto não possui tags html nada é feito
     * @param Tag|string $content Conteudo que sera analisado e parseado se necessário
     * @return array<string|Tag> Lista de strings e Tags que formam o conteudo do elemento
     * 
     * @todo Considerar alterar o retorno para Tags
     */
    private function parseInternal($content) {
        if ($content instanceof Tag) return array($content);
        if (strlen($content) == strlen(strip_tags($content)))
            return array($content);
        else {
            return self::parse($content);
        }
    }

    /**
     * Adiciona um pai para o elemento.
     * Cuidado ao utilizar este metodo pois ele não deveria estar sendo utilizado
     * Se o objeto já possui um pai, ele será removido
     * 
     * @param Tag $parent Pai do elemento
     * @return Tag
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
    
    /* Metodos estaticos base */
    
    /**
     * Transforma texto html em elementos Tags
     * @param string $html Conteudo HTML que deverá ser parseado
     * @return array<Tag> Retorna um vetor com as tags que estão na raiz
     * 
     * @todo Convert return type (from  array to Tags)
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
        var_dump($tag);
        $list = array();
        foreach ($nodes as $node) {
            $list[] = self::parseNode($node);
        }
        
        return $list;
    }
    
    /**
     * Recebe um node e realiza o parser (função recursiva)
     * @param array $node Formato especifico retornado pele metodo HtmlParser::toArray
     * @return \Tag
     */
    private static function parseNode($node) {
        // Create obj
        $tagName = ucfirst($node['tag']);
        if (class_exists($tagName)) {
            $obj = new $tagName();
        }
        else {
            $obj = new GenericTag($tagName);
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
}
