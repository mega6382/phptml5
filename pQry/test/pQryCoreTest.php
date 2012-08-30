<?php

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-08-29 at 15:50:31.
 */
class pQryCoreTest extends PHPUnit_Framework_TestCase {

    /**
     * @var pQryCore
     */

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers pQryCore::isSelector
     * @covers pQryCore::getRules
     * @covers pQryCore::cleanSelector
     * @group  pQry
     */
    public function testIsSelector() {
        $this->assertFalse(pQryCore::isSelector(''));
        $this->assertEmpty(pQryCore::getRules(''));
        $this->assertTrue(pQryCore::isSelector('*'));
        $rules = pQryCore::getRules('*');
        $this->assertNotEmpty($rules);
        $this->assertCount(1, $rules);
        $this->assertEquals('*', $rules[0]['tag']);
        $this->assertTrue(pQryCore::isSelector('.class'));
        $r1 = pQryCore::getRules('.class');
        $this->assertEquals('class', $r1[0]['class']);
        $this->assertTrue(pQryCore::isSelector('*.class'));
        $r2 = pQryCore::getRules('*.class');
        $this->assertEquals('class', $r2[0]['class']);
        $this->assertEquals('*', $r2[0]['tag']);
        $this->assertTrue(pQryCore::isSelector('#id'));
        $r3 = pQryCore::getRules('#id');
        $this->assertEquals('id', $r3[0]['id']);
        $this->assertTrue(pQryCore::isSelector('[type]'));
        $r4 = pQryCore::getRules('[type]');
        $this->assertCount(1, $r4[0]['attr']);
        $this->assertTrue(in_array('type', $r4[0]['attr']));
        $this->assertFalse(pQryCore::isSelector('[]'));
        $this->assertTrue(pQryCore::isSelector('[type=text]'));
        $r5 = pQryCore::getRules('[type=text]');
        $this->assertArrayHasKey('type', $r5[0]['attr']);
        $this->assertEquals('text', $r5[0]['attr']['type']['value']);
        $this->assertEquals('=', $r5[0]['attr']['type']['op']);
        $this->assertTrue(pQryCore::isSelector('[type$=text]'));
        $r6 = pQryCore::getRules('[type$=text]');
        $this->assertArrayHasKey('type', $r6[0]['attr']);
        $this->assertEquals('text', $r6[0]['attr']['type']['value']);
        $this->assertEquals('$=', $r6[0]['attr']['type']['op']);
        $this->assertTrue(pQryCore::isSelector(':hidden'));
        $r7 = pQryCore::getRules(':hidden');
        $this->assertTrue(in_array('hidden', $r7[0]['pseudo']));
        $this->assertTrue(pQryCore::isSelector('div'));
        $r8 = pQryCore::getRules('div');
        $this->assertEquals('div', $r8[0]['tag']);
        
        $ops = array(
            array('blockquote','#meudiv','id','meudiv'),
            array('h1','.minhaclasse','class','minhaclasse'),
            array('span',':enabled','pseudo',array('enabled')),
            array('span','[name]','attr',array('name')),
            array('span','[name^=xyz]','attr',array('name'=>array('op'=>'^=','value'=>'xyz'))),
            array('span','[name^=xyz][title!="Alo"]','attr',array(
                        'name'=>array('op'=>'^=','value'=>'xyz'), 
                        'title'=>array('op'=>'!=','value'=>'Alo'))
                ),
        );
        
        foreach ($ops as $op) {
            $this->assertTrue(pQryCore::isSelector($op[0].$op[1]));
            $rules = pQryCore::getRules($op[0].$op[1]);
            $this->assertEquals($op[0], $rules[0]['tag']);
            $this->assertEquals($op[3], $rules[0][$op[2]]);
        }
        
        $conectors = array('descendant' => ' ', 'next' => ' + ', 'child' => ' > ', 'siblings' => ' ~ ');
        foreach ($conectors as $c => $v) {
            $this->assertTrue(pQryCore::isSelector('*'. $v .' p')); 
            $this->assertEquals(array(array('tag'=>'*',$c=>array('tag'=>'p'))), 
                    pQryCore::getRules('*'. $v .' p'));
             
            $this->assertEquals(array(array('tag'=>'blockquote',$c=>array('class'=>'p'))), 
                    pQryCore::getRules('blockquote '. $v .' .p'));
            
            $this->assertTrue(pQryCore::isSelector('.test'. $v .'#id')); 
            $this->assertEquals(array(array('class'=>'test',$c=>array('id'=>'id'))), 
                    pQryCore::getRules('.test'. $v .'#id'));
            
            $this->assertTrue(pQryCore::isSelector('#i'. $v .'*')); 
            $this->assertEquals(array(array('id'=>'i',$c=>array('tag'=>'*'))), 
                    pQryCore::getRules('#i'. $v .'*'));
            
            $this->assertTrue(pQryCore::isSelector('[name]'. $v .':input')); 
            $this->assertEquals(array(array('attr'=>array('name'),$c=>array('pseudo'=>array('input')))),
                    pQryCore::getRules('[name]'. $v .':input'));
            
            $this->assertTrue(pQryCore::isSelector('div:button'. $v .'#id[x!=y]')); 
            $this->assertEquals(array(array('tag'=>'div', 'pseudo'=>array('button'),$c=>array('id'=>'id', 
                'attr'=>array('x'=>array('op'=>'!=', 'value'=>'y'))))), 
                    pQryCore::getRules('div:button'. $v .'#id[x!=y]'));
        }
        
        $this->assertTrue(pQryCore::isSelector('div:button > #id[x!=y] + [name][type=text]')); 
        $this->assertEquals(array(array(  'tag'=>'div', 
                                    'pseudo'=>array('button'),
                                    'child'=> array( 'id'=>'id', 
                                                     'attr'=>array('x'=>array('op'=>'!=', 'value'=>'y')),
                                                     'next'=>array(
                                                         'attr' => array(
                                                             'name',
                                                             'type' => array('op'=>'=', 'value'=>'text')
                                                         )
                                                     )
                                              )
                                  )), 
                pQryCore::getRules('div:button > #id[x!=y] + [name][type=text]'));
        
        $this->assertTrue(pQryCore::isSelector('div:button, #id[x!=y], [name][type=text]')); 
        $double = pQryCore::getRules('div:button, #id[x!=y], [name][type=text]');
        $this->assertCount(3, $double);
        $this->assertEquals(array('tag'=>'div','pseudo'=>array('button')), $double[0]);
        $this->assertEquals(array('id'=>'id', 'attr'=>array('x'=>array('op'=>'!=', 'value'=>'y'))), $double[1]);
        $this->assertEquals(array('attr'=>array('name', 'type' => array('op'=>'=', 'value'=>'text'))), $double[2]);
    }

    /**
     * @covers pQryCore::select
     * @covers pQryCore::executeRules
     * @group pQrytest
     */
    public function testSelect() {
        $target = new pQryHTML('body');
        $elements = array('div','p','h1','footer');
        $objs = $target->toArray();
        foreach ($elements as $elm) {
            $$elm = new pQryHTML($elm);
            $target->append($$elm);
            $objs[] = $$elm;
        }
        
        $s1 = pQryCore::select($target, array('tag' => '*'), array('deep'=>false));
        $this->assertCount(1, $s1);
        $this->assertTrue(in_array($target, $s1));
        
        $s2 = pQryCore::select($target, array('tag' => '*'));
        $this->assertCount(count($elements)+1, $s2);
        $this->assertTrue(in_array($target, $s2));
        foreach ($elements as $elem)
            $this->assertTrue(in_array($$elem, $s2));
        
        $this->assertEquals($s2, pQryCore::select($objs, array('tag' => '*')));
        $this->assertEquals($s2, pQryCore::select($objs, array('tag' => '*'), array('deep'=>false)));
        $this->assertCount(2, pQryCore::select($objs, array('tag' => '*'), array('max'=>2)));
        $s3 = pQryCore::select($objs, array('tag' => '*'), array('until'=>$h1));
        $this->assertCount(3, $se);
        $this->assertTrue(in_array($target, $s3));
        $this->assertTrue(in_array($div, $s3));
        $this->assertTrue(in_array($p, $s3));
        $this->assertFalse(in_array($h1, $s3));
        $this->assertFalse(in_array($footer, $s3));
        
        $this->assertEquals(array($div), pQryCore::select($target, 'div'));
        $this->assertEquals(array($h1), pQryCore::select($target, ':header'));
        
        $footer->attr('title', 'Footer message');
        $this->assertEquals(array($h1, $footer), pQryCore::select($target, ':header, [title]'));
        
        $a1 = new pQryHTML("a");
        $footer->append($a1);
        $this->assertEquals(array($footer), pQryCore::select($a1, '[title]', array('dir'=>'up')));
        $this->assertCount(2, pQryCore::select($a1, '*', array('dir'=>'up')));
        $this->assertCount(1, pQryCore::select($a1, '*', array('dir'=>'up', 'deep'=>false)));
        
    }

    /**
     * @covers pQryCore::getEmptyObject
     * @group pQry
     */
    public function testGetEmptyObject() {
        $obj = pQryCore::getEmptyObject();
        $this->assertSame($obj, pQryCore::getEmptyObject());
        $this->assertNotEmpty($obj);
        $this->assertEquals(0, $obj->size());
        $newobj = new pQryEmpty();
        $this->assertNotSame($obj, $newobj);
    }
}