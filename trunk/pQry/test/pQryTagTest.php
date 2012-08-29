<?php
/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-08-16 at 08:18:03.
 */
class pQryTagTest extends PHPUnit_Framework_TestCase {

    /**
     * @var pQryTag
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new pQryHTML('div');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public function setAttributes() {
        $data = array('dt1' => '2px', 'dt2' => '100%', 'dt3' => 2);
        $this->assertEquals($this->object, $this->object->data($data));
        $data1 = array('title' => 'Other test', 'lang' => 'aaaaa', 'id' => 'myID[9]:_zZ');
        $this->assertEquals($this->object, $this->object->attr($data1));
        $data2 = array('margin' => '2px', 'width' => '100%', 'background-image' => 'url(http://testeURL.jpg)');
        $this->assertEquals($this->object, $this->object->css($data2));
        return array('attr' => $data1, 'data'=>$data, 'css'=>$data2);
    }

    /**
     * @covers pQryTag::addClass
     * @covers pQryTag::hasClass
     * @covers pQryTag::removeClass
     * @group phptml5
     */
    public function testClass() {
        $this->assertEquals($this->object, $this->object->addClass('className'));
        $this->assertEquals('className', $this->object->attr('class'));
        $this->assertTrue($this->object->hasClass('className'));
        
        $this->assertEquals($this->object, $this->object->addClass('otherClassName     moreOne'));
        $this->assertEquals('className otherClassName moreOne', $this->object->attr('class'));
        
        $this->assertTrue($this->object->hasClass('className'));
        $this->assertTrue($this->object->hasClass('otherClassName'));
        $this->assertTrue($this->object->hasClass('moreOne'));
        $this->assertTrue($this->object->hasClass('className moreOne'));
        $this->assertTrue($this->object->hasClass('otherClassName className'));
        $this->assertFalse($this->object->hasClass('moreTwo'));
        $this->assertFalse($this->object->hasClass('moreTwo className'));
        $this->assertFalse($this->object->hasClass('className moreTwo'));
        
        $cls = $this->object->attr('class');
        $this->assertEquals($this->object, $this->object->addClass('cls1 cls2 cls3 cls4 '));
        $this->assertEquals($this->object, $this->object->removeClass('cls1'));
        $this->assertFalse($this->object->hasClass('cls1'));
        $this->assertEquals($this->object, $this->object->removeClass('cls2 cls3 cls4'));
        $this->assertFalse($this->object->hasClass('cls1 cls2 cls3 cls4'));
        $this->assertEquals($cls, $this->object->attr('class'));
        
        $this->object->id('meuId');
        function aux ($ele, $class) {
            return $class . ' maisUma ' . $ele->id();
        }
        $cls .= ' maisUma ' . $this->object->id();
        $this->assertEquals($cls, $this->object->addClass('aux')->attr('class'));
    }

    /**
     * @covers pQryTag::append
     * @group phptml5
     */
    public function testAfter() {
        $div = new pQryHTML('div');
        $div->append($this->object);
        $p1 = new pQryHTML('p');
        $p2 = new pQryHTML('p');
        
        $p1->html('P1');
        $p2->html('P2');
        
        $html = '<div class="nome">Primeiro</div>';
        $this->assertEquals($this->object, $this->object->append($html));
        $this->assertEquals('<div>' . $html . '</div>', $this->object->toString());
        
        $html = '<div>'. $html .'</div>';
        $this->assertEquals($this->object, $this->object->after($p2)->after($p1));
        $html = '<div>' . $html . '<p>P1</p><p>P2</p></div>';
        $this->assertEquals($html, $div->toString());
        
        // root element doesn't has parent / return just element
        $tags = $div->after('<a href="http://#">link</a>')->parent();
        $this->assertTrue($tags instanceof pQryEmpty);
        $this->assertEquals( $html . '<a href="http://#">link</a>', $tags->toString());
    }
    
    /**
     * @covers pQryTag::append
     * @covers pQryTag::appendTo
     * @covers pQryTag::size
     * @group phptml5
     */
    public function testAppend() {
        $p1 = new pQryHTML('p');
        $p2 = new pQryHTML('p');
        $p3 = new pQryHTML('p');
        
        $this->assertEquals($this->object, $this->object->append($p1));
        $this->assertEquals($this->object, $this->object->append($p2)->append($p3));
        $this->assertEquals(3, $this->object->size());
        $this->assertEquals('<div><p></p><p></p><p></p></div>', $this->object->toString());
        
        $this->assertEquals('Paragraph3', $p3->append('Paragraph3')->text());
        
        $span1 = new pQryHTML('span');
        $span2 = new pQryHTML('span');
        $a = new pQryHTML('a');
        
        $this->assertEquals($p1, $p1->append($span1));
        $this->assertEquals($p2, $p2->append($span2)->append($a));
        
        $this->assertEquals(3, $this->object->size());
        $this->assertEquals(1, $p1->size());
        $this->assertEquals(2, $p2->size());
        $this->assertEquals('<div><p><span></span></p><p><span></span><a></a></p><p>Paragraph3</p></div>', $this->object->toString());
        
        $x = new pQryHTML('div');
        $x->html('<p class="parag">Este eh um paragrafo</p>');
        $x->appendTo(new pQryHTML('body'));
        $this->assertEquals('<body><div><p class="parag">Este eh um paragrafo</p></div></body>', $x->parent()->toString());
    }

    /**
     * @covers pQryTag::attr
     * @covers pQryTag::removeAttr
     * @group phptml5
     */
    public function testAttr() {
        $this->assertEquals($this->object, $this->object->attr('tiTle', 'My title'));
        $this->assertEquals('My title', $this->object->attr('title'));
        $this->assertEquals($this->object, $this->object->attr(' ti<i>tle ', '  My"<b>  title  '));
        $this->assertEquals('My&quot;  title', $this->object->attr('titl<b>e'));
        $this->assertEquals($this->object, $this->object->attr('ti&tle', 'finaltest'));
        $this->assertEquals('finaltest', $this->object->attr('t&itle'));
        
        $this->assertEmpty($this->object->attr('lang'));
        
        $data = array('title' => 'Other test', 'lang' => '   aaaaa   ', 'id' => 'myID[9]:_zZ');
        $this->assertEquals($this->object, $this->object->attr($data));
        foreach ($data as $k => $v) {
            $this->assertEquals(trim($v), $this->object->attr($k));
            $this->assertEquals($this->object, $this->object->removeAttr($k));
            $this->assertEmpty($this->object->attr($k));
        }
        
        $this->assertEquals($this->object, $this->object->removeAttr("Invalido"));
        $this->assertEquals($this->object, $this->object->removeAttr(""));
        
        $attr = array("id"=>"a1");
        $obj = new pQryHTML('pre');
        $this->assertEquals('a1', $obj->attr($attr)->id());
  
    }

    /**
     * @covers pQryTag::children
     * @todo   Implement testChildren().
     */
    public function testChildren() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers pQryTag::cloneThis
     * @covers pQryTag::__clone
     * @group  phptml5
     */
    public function testCloneThis() {
        $p = new pQryHTML('p');
        $a = new pQryHTML('a');
        
        $a->attr('title', 'Titulo A');
        $p->append('Teste de clone')->addClass('pclass')->append($a);
        
        $p1 = $p->cloneThis();
        
        $this->assertEquals($p->toString(), $p1->toString());
        $p->removeClass('pclass');
        $this->assertNotEquals($p->toString(), $p1->toString());
        $p->addClass('pclass');
        $this->assertEquals($p->toString(), $p1->toString());
        $a->attr('title', 'Novo Titulo A');
        $this->assertNotEquals($p->toString(), $p1->toString());
        $a->append('Ola Mundo');
        $a1 = clone $a;
        $this->assertNotEquals($a, $a1);
        $this->assertNotEquals($a->parent(), $a1->parent());
        $this->assertEquals($a->toString(), $a1->toString());
        $a->id('idA');
        $a1->id('idB');
        $this->assertNotEquals($a->id(), $a1->id());
    }

    /**
     * @covers pQryTag::css
     * @group phptml5
     */
    public function testCss() {
        $this->assertEquals($this->object, $this->object->css('border', '1px solid red'));
        $this->assertEquals('1px solid red', $this->object->css('border'));
        $this->assertEquals('border:1px solid red;', $this->object->attr('style'));
        
        $this->assertEquals($this->object, $this->object->css('border', '2px solid red;'));
        $this->assertEquals('2px solid red', $this->object->css('border'));
        $this->assertEquals('border:2px solid red;', $this->object->attr('style'));
        
        $this->assertEmpty($this->object->css('margin'));
        
        $style = 'border:2px solid red; ';
        $data = array('margin' => '2px', 'width' => '100%', 'background-image' => 'url(http://testeURL.jpg)');
        $this->assertEquals($this->object, $this->object->css($data));
        foreach ($data as $k => $v) {
            $style .= $k . ':' . $v . '; ';
            $this->assertEquals($v, $this->object->css($k));
        }
        $this->assertEquals(trim($style), $this->object->attr('style'));
        
        $this->assertEquals($this->object, $this->object->css('border', ''));
        $this->assertEmpty($this->object->css('border'));
        $this->assertEquals(str_replace('border:2px solid red; ', '', trim($style)), $this->object->attr('style'));
    }

    /**
     * @covers pQryTag::data
     * @covers pQryTag::removeData
     * @group phptml5
     */
    public function testData() {
        $this->assertEquals($this->object, $this->object->data('data1', 'data1'));
        $this->assertEquals('data1', $this->object->data('data1'));
        $this->assertEquals('data1', $this->object->attr('data-data1'));
        
        $this->assertEquals($this->object, $this->object->data('data1', 'DATA1'));
        $this->assertEquals('DATA1', $this->object->data('data1'));
        $this->assertEquals('DATA1', $this->object->attr('data-data1'));
       
        $data = array('dt1' => '2px', 'dt2' => '100%', 'dt3' => 2);
        $this->assertEquals($this->object, $this->object->data($data));
        foreach ($data as $k => $v) {
            $this->assertEquals($v, $this->object->data($k));
            $this->assertEquals($this->object, $this->object->removeData($k));
            $this->assertEmpty($this->object->data($k));
            $this->assertEmpty($this->object->attr('data-' . $k));
        }
    }

    /**
     * @covers pQryTag::emptyContent
     * @group  phptml5
     */
    public function testEmptyContent() {
        $p = new pQryHTML('p');
        $p->attr('title', 'teste')->append(new pQryHTML('a'));
        
        $this->assertEquals('<div><p title="teste"><a></a></p></div>', $this->object->append($p)->toString());
        $this->assertEquals('<div>Teste<p title="teste"><a></a></p></div>', $this->object->prepend('Teste')->toString());
        $this->assertEquals('<div class="x">Teste<p title="teste"><a></a></p></div>', $this->object->addClass('x')->toString());
        
        $this->assertEquals('<div class="x"></div>', $this->object->emptyContent()->toString());
        $this->assertEquals('<p title="teste"><a></a></p>', $p->toString());
        $this->assertEquals(1, $p->size());
        $this->assertEquals('<p title="teste"></p>', $p->emptyContent()->toString());
        $this->assertEquals(0, $p->size());
    }

    /**
     * @covers pQryTag::emptyAttr
     * @group phptml5
     */
    public function testEmptyAttr() {
        $list = $this->setAttributes();
        $this->assertNotEmpty($this->object->attr('style'));
        $this->assertNotEmpty($this->object->attr('title'));
        $this->assertEquals($this->object, $this->object->emptyAttr());
        
        foreach ($list['attr'] as $attr) {
            $this->assertEmpty($this->object->attr($attr));
        }
        
        $this->assertEquals('<div></div>', $this->object->toString());
        $this->object->append(new pQryHTML('p'))->append('Teste')->addClass('x');
        $this->assertEquals('<div><p></p>Teste</div>', $this->object->emptyAttr()->toString());
    }

    /**
     * @covers pQryTag::emptyAll
     * @group phptml5
     */
    public function testEmptyAll() {
        $this->assertEquals('<div></div>', $this->object->toString());
        $this->setAttributes();
        $this->assertNotEmpty($this->object->attr('style'));
        $this->assertNotEmpty($this->object->attr('title'));
        
        $p = new pQryHTML('p');
        $this->assertEquals(2, $this->object->prepend($p->attr('title', 'teste'))->append(new pQryHTML('a'))->size());
        
        $this->assertNotEquals('<div></div>', $this->object->toString());
        $this->assertEquals('<div></div>', $this->object->emptyAll()->toString());
    }


    /**
     * @covers pQryTag::html
     * @todo   Implement testHtml().
     */
    public function testHtml() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers pQryTag::id
     * @group  phptml5
     */
    public function testId() {
        $this->assertEquals($this->object, $this->object->id('xyz'));
        $this->assertEquals('xyz', $this->object->id());
        $this->assertEquals('xyz', $this->object->attr('id'));
        
        $this->assertEquals($this->object, $this->object->id('xyz3'));
        $this->assertEquals('xyz3', $this->object->id());
        $this->assertEquals('xyz3', $this->object->attr('id'));
    }

    /**
     * @covers pQryTag::match
     * @group phptml5
     */
    public function testMatch() {
        $rulelist = array();
        $p = new pQryHTML('p');
        $p->addClass('class1')->id('pp')->attr('title', 'Title of p')->attr('lang','pt-br');
        $div = new pQryHTML('div');
        $div->addClass('class1 class2')->id('dd')->attr('title', 'Div Title')->attr('lang','pt');
        
        $this->assertTrue($p->match($rulelist));
        $this->assertTrue($p->match($rulelist));
        
        // Tag
        $rulelist = array('tag'=>'p');
        $this->assertTrue($p->match($rulelist));
        $this->assertFalse($div->match($rulelist));
        
        $rulelist = array('tag'=>'*');
        $this->assertTrue($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        // Class
        $rulelist = array('class'=>'class1');
        $this->assertTrue($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        $rulelist = array('class'=>'class2');
        $this->assertFalse($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        // ID
        $rulelist = array('id'=>'dd');
        $this->assertFalse($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        $rulelist = array('id'=>'ddd');
        $this->assertFalse($p->match($rulelist));
        $this->assertFalse($div->match($rulelist));
        
        // Attributes
        $rulelist = array('attr'=>array('title'));
        $this->assertTrue($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        $rulelist['attr'][] = 'src';
        $this->assertFalse($p->match($rulelist));
        $this->assertFalse($div->match($rulelist));
        
        // Attributes operators
        $rulelist = array('attr'=>array('title'=>array('op'=>'=', 'value'=>'Title of p')));
        $this->assertTrue($p->match($rulelist));
        $this->assertFalse($div->match($rulelist));
        
        $rulelist['attr']['title']['op'] = '!=';
        $this->assertFalse($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        $rulelist = array('attr'=>array('title'=>array('op'=>'^=', 'value'=>'Title')));
        $this->assertTrue($p->match($rulelist));
        $this->assertFalse($div->match($rulelist));
        
        $rulelist = array('attr'=>array('title'=>array('op'=>'$=', 'value'=>'itle')));
        $this->assertFalse($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        $rulelist = array('attr'=>array('title'=>array('op'=>'*=', 'value'=>'iv')));
        $this->assertFalse($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        $rulelist = array('attr'=>array('class'=>array('op'=>'~=', 'value'=>'class2')));
        $this->assertFalse($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        $rulelist = array('attr'=>array('lang'=>array('op'=>'|=', 'value'=>'pt')));
        $this->assertTrue($p->match($rulelist));
        $this->assertTrue($div->match($rulelist));
        
        $rulelist = array('attr'=>array('lang'=>array('op'=>'|=', 'value'=>'pts')));
        $this->assertFalse($p->match($rulelist));
        $this->assertFalse($div->match($rulelist));
        
        // Pseudoelements
        $button = new pQryHTML('button');
        $this->assertTrue($button->match(array('pseudo'=>array(':button'))));
        $this->assertFalse($p->match(array('pseudo'=>array(':button'))));
        
        $checkbox = new pQryHTML('input');
        $checkbox->attr('type', 'checkbox');
        $this->assertTrue($checkbox->match(array('pseudo'=>array(':checkbox'))));
        $this->assertTrue($checkbox->match(array('pseudo'=>array('checkbox'))));
        $this->assertFalse($button->match(array('pseudo'=>array(':checkbox'))));
        
        $this->assertFalse($checkbox->match(array('pseudo'=>array(':checked'))));
        $checkbox->prop('checked', true);
        $this->assertTrue($checkbox->match(array('pseudo'=>array(':checked'))));
        
        $this->assertFalse($checkbox->match(array('pseudo'=>array(':disabled'))));
        $checkbox->prop('disabled', true);
        $this->assertTrue($checkbox->match(array('pseudo'=>array(':disabled'))));
        
        $this->assertFalse($checkbox->match(array('pseudo'=>array(':enabled'))));
        $this->assertTrue($button->match(array('pseudo'=>array(':enabled'))));
        
        $this->assertTrue($button->match(array('pseudo'=>array(':empty'))));
        $button->html('Click to edit');
        $this->assertFalse($button->match(array('pseudo'=>array(':empty'))));
        
        $h1 = new pQryHTML('h1');
        $this->assertTrue($h1->match(array('pseudo'=>array(':header'))));
        $this->assertFalse($button->match(array('pseudo'=>array(':header'))));
        
        $this->assertFalse($h1->match(array('pseudo'=>array(':input'))));
        $this->assertFalse($button->match(array('pseudo'=>array(':input'))));
        $this->assertTrue($checkbox->match(array('pseudo'=>array(':input'))));
        
        $button->attr('type', 'reset');
        $this->assertTrue($button->match(array('pseudo'=>array(':reset'))));
        $this->assertFalse($checkbox->match(array('pseudo'=>array(':reset'))));
        
        $opt1 = new pQryHTML('option');
        $opt1->prop('selected', true);
        $opt2 = new pQryHTML('option');
        
        $this->assertTrue($opt1->match(array('pseudo'=>array(':selected'))));
        $this->assertFalse($opt2->match(array('pseudo'=>array(':selected'))));
        
    }
    
    /**
     * @covers pQryTag::prepend
     * @group phptml5
     */
    public function testPrepend() {
        $p1 = new pQryHTML('p');
        $p2 = new pQryHTML('p');
        $p3 = new pQryHTML('p');
        
        $this->assertEquals($this->object, $this->object->prepend($p1));
        $this->assertEquals($this->object, $this->object->prepend($p2)->prepend($p3));
        $this->assertEquals(3, $this->object->size());
        $this->assertEquals('<div><p></p><p></p><p></p></div>', $this->object->toString());
        
        $this->assertEquals('Paragraph3', $p3->prepend('Paragraph3')->text());
        
        $span1 = new pQryHTML('span');
        $span2 = new pQryHTML('span');
        $a = new pQryHTML('a');
        
        $this->assertEquals($p1, $p1->prepend($span1));
        $this->assertEquals($p2, $p2->prepend($span2)->prepend($a));
        
        $this->assertEquals(3, $this->object->size());
        $this->assertEquals(1, $p1->size());
        $this->assertEquals(2, $p2->size());
        $this->assertEquals('<div><p>Paragraph3</p><p><a></a><span></span></p><p><span></span></p></div>', $this->object->toString());
    }

    /**
     * @covers pQryTag::prop
     * @covers pQryTag::removeProp
     * @group phptml5
     */
    public function testProp() {
        $this->assertEquals($this->object, $this->object->prop('prop1', true));
        $this->assertTrue($this->object->prop('prop1'));
        $this->assertEquals('prop1', $this->object->attr('prop1'));
        
        $this->assertEquals($this->object, $this->object->prop('prop2', false));
        $this->assertFalse($this->object->prop('prop2'));
        $this->assertEmpty($this->object->attr('prop2'));
        
        $this->assertEquals($this->object, $this->object->removeProp('prop1'));
        $this->assertFalse($this->object->prop('prop1'));
        $this->assertEmpty($this->object->attr('prop1'));
        
        $this->assertEquals($this->object, $this->object->removeProp('prop2'));
        $this->assertFalse($this->object->prop('prop2'));
        $this->assertEmpty($this->object->attr('prop2'));
    }

    /**
     * @covers pQryTag::remove
     * @covers pQryTag::parent
     * @group  phptml5
     */
    public function testRemove() {
        $this->object->id('div');
        $this->assertTrue($this->object->parent() instanceof pQryEmpty);
        $this->assertTrue($this->object->parent()->parent() instanceof pQryObj);
        $p = new pQryHTML('p');
        $p->id('p');
        $div = new pQryHTML('div');
        $div->id('div2')->append($p)->prepend('Result: ');

        for ($i=0; $i < 3; $i++) {
            $id = 'i' . $i;
            $div->id($id);
            $p->append($this->object);
            $this->assertEquals($p, $this->object->parent());
            $this->assertEquals($div, $p->parent());

            $this->assertEquals('<div id="'. $id .'">Result: <p id="p"><div id="div"></div></p></div>', $div->toString());
            $this->assertEquals('<div id="'. $id .'">Result: <p id="p"></p></div>', $p->remove($this->object)->parent()->toString());

            $this->assertTrue($this->object->parent() instanceof pQryEmpty);
            $div->prepend($this->object);

            $this->assertEquals($div, $this->object->parent());
            $this->assertEquals('<div id="'. $id .'"><div id="div"></div>Result: <p id="p"></p></div>', $div->toString());
        }
        
        //Referencia cruzada direta
        $x = new pQryHTML('x');
        $y = new pQryHTML('y');
        $z = new pQryHTML('z');
        $x->id('x'); $y->id('y'); $z->id('z');
        $x->append($y);
        $this->assertEquals($x, $y->parent());
        $y->append($x);
        $this->assertEquals($y, $x->parent());
        $this->assertNotEquals($x, $y->parent());
        $x->append($z);
        $this->assertEquals($x, $z->parent());
        $z->append($x);
        $this->assertEquals($z, $x->parent());
        $this->assertNotEquals($x, $z->parent());
    }

    /**
     * @covers pQryTag::siblings
     * @todo   Implement testSiblings().
     */
    public function testSiblings() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers pQryTag::text
     * @group  phptml5
     */
    public function testText() {
       $this->assertEquals('Ola Mundo', $this->object->text('Ola Mundo')->text());
       $this->assertEquals('Troca Texto', $this->object->text('Troca Texto')->text());
       $this->assertEquals('&lt;b&gt;Ola Mundo&lt;/b&gt;', $this->object->text('<b>Ola Mundo</b>')->text());
    }

    /**
     * @covers pQryTag::toString
     * @covers pQryTag::__toString
     * @group phptml5
     */
    public function testToString() {
        $html = '<div></div>';
        $this->assertEquals($html, $this->object->toString());
        
        $this->object->attr('title', 'Teste');
        $html = '<div title="Teste"></div>';
        $this->assertEquals($html, $this->object->toString());
        $this->assertEquals($html, '' . $this->object);
        
        $this->object->id('myId[]');
        $html = '<div title="Teste" id="myId[]"></div>';
        $this->assertEquals($html, $this->object->toString());
        
        $this->object->addClass('cls1  cls2');
        $html = '<div title="Teste" id="myId[]" class="cls1 cls2"></div>';
        $this->assertEquals($html, $this->object->toString());
    }
    
     /**
     * @covers pQryTag::parse
     * @group phptml5
     */
    public function testParse() {
        $htmls = array(
            '<h1>Ola Mundo</h1>',
            '<div>Ola Mundo <span>Teste XYZ</span></div>',
            '<div>Ola Mundo <span>Teste XYZ</span><blockquote>Ola Mundo 1<span>Teste XYZ 1</span></blockquote></div>',
            '<h1 id="a1">Ola Mundo</h1>',
            '<div>Ola Mundo <span class="xyz htz">Teste XYZ</span> Mais um texto</div>',
            '<div>Ola Mundo <a href="http://www.uol.com.br">Teste XYZ</a><blockquote>Ola Mundo 1<span>Teste XYZ 1</span></blockquote></div>',
            array(2, '<div>Ola Mundo <span>Teste XYZ</span></div><h1 id="a2">Ola Mundo</h1>'),
        );
        
        $total = 0;
        $all = "";
        foreach ($htmls as $html) { 
            $qtd = 1;
            if (is_array($html)) {
                $qtd = $html[0];
                $html = $html[1];
            }
            $obj = pQryTag::parse($html);
            $this->assertEquals($qtd, count($obj));
            for ($i=0; $i < $qtd; $i++)
                $this->assertTrue($obj[$i] instanceof pQryTag);
            
            $expected = "";
            foreach ($obj as $o) $expected .= $o->toString ();
            $this->assertEquals($html, $expected);
            
            $total += $qtd;
            $all .= $expected;
        }
        
        // Gran Finalle
        $obj = pQryTag::parse('<body>'.$all.'</body>');
        $this->assertEquals('<body>'.$all.'</body>', $obj[0]->toString());
        $this->assertEquals(1, count($obj));
        
        $obj = pQryTag::parse($all);
        $this->assertEquals($total, count($obj));
        foreach ($htmls as $i => $html) {
             if (is_array($html)) {
                 $expected = "";
                 for ($j = 0; $j < $html[0]; $j++) {
                    $expected .= $obj[$i + $j]->toString();
                 }
                 $this->assertEquals($html[1], $expected);
             }
             else {
                $this->assertEquals($html, $obj[$i]->toString());
             }
        }
        
        //TODO Parse with errors (What to do)
    }
}
