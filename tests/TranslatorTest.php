<?php

use PHPUnit\Framework\TestCase;
use Sy\Component;

#region Composition 0
class A extends Component {

	public function __construct() {
		$this->mount(function () {
			$this->setTemplateContent('<a>{HELLO} {"world"} {B}</a>');
			$this->setVar('HELLO', $this->_('hello'));
			$this->setVar('B', new B());
		});
	}

}

class B extends Component {

	public function __construct() {
		$this->mount(function () {
			$this->setTemplateContent('<b>{"hello"} {"world"} {C}</b>');
			$this->setVar('C', new C());
		});
	}

}

class C extends Component {

	public function __construct() {
		$this->mount(function () {
			$this->setTemplateContent('<!-- BEGIN C --><c{N}>{"hello"} {"world"}</c><!-- END C -->');
			for ($i = 0; $i < 3; $i++) {
				$this->setBlock('C', ['N' => $i]);
			}
		});
	}

}

class P extends Component {

	public function __construct() {
		$this->mount(function () {
			$this->setTemplateContent('{BODY}');
			$this->addTranslator(__DIR__ . '/lang', 'php', 'fr');
			$this->setVar('BODY', new A());
		});
	}

}
#endregion

#region Composition 1
class A1 extends Component {

	public function __toString() {
		$this->mount(function () {
			$this->setTemplateContent('<a>{HELLO} {"world"} {B}</a>');
			$this->setVar('HELLO', $this->_('hello'));
			$this->setVar('B', new B1());
		});
		return parent::__toString();
	}

}

class B1 extends Component {

	public function __toString() {
		$this->mount(function () {
			$this->setTemplateContent('<b>{"hello"} {"world"} {C}</b>');
			$this->setVar('C', new C1());
		});
		return parent::__toString();
	}

}

class C1 extends Component {

	public function __toString() {
		$this->mount(function () {
			$this->setTemplateContent('<!-- BEGIN C --><c{N}>{"hello"} {"world"}</c><!-- END C -->');
			for ($i = 0; $i < 3; $i++) {
				$this->setBlock('C', ['N' => $i]);
			}
		});
		return parent::__toString();
	}

}

class P1 extends Component {

	public function __construct() {
		$this->mount(function () {
			$this->setTemplateContent('{BODY}');
			$this->addTranslator(__DIR__ . '/lang', 'php', 'fr');
			$this->setVar('BODY', new A1());
		});
		return parent::__toString();
	}

}
#endregion

#region Composition 2
class A2 extends Component {

	public function render() {
		$this->mount(function () {
			$this->setTemplateContent('<a>{HELLO} {"world"} {B}</a>');
			$this->setVar('HELLO', $this->_('hello'));
			$this->setVar('B', new B2());
		});
		return parent::render();
	}

}

class B2 extends Component {

	public function render() {
		$this->mount(function () {
			$this->setTemplateContent('<b>{"hello"} {"world"} {C}</b>');
			$this->setVar('C', new C2());
		});
		return parent::render();
	}

}

class C2 extends Component {

	public function render() {
		$this->mount(function () {
			$this->setTemplateContent('<!-- BEGIN C --><c{N}>{"hello"} {"world"}</c><!-- END C -->');
			for ($i = 0; $i < 3; $i++) {
				$this->setBlock('C', ['N' => $i]);
			}
		});
		return parent::render();
	}

}

class P2 extends Component {

	public function render() {
		$this->mount(function () {
			$this->setTemplateContent('{BODY}');
			$this->addTranslator(__DIR__ . '/lang', 'php', 'fr');
			$this->setVar('BODY', new A2());
		});
		return parent::render();
	}

}
#endregion

#region Composition 3
class A3 extends Component {

	public function __construct() {
		$this->mount(function () {
			$this->addTranslator(__DIR__ . '/lang', 'php', 'fr');
			$this->setTemplateContent('<a>{HELLO/} {"world"} {B}</a>');
			$this->setVars([
				'HELLO' => $this->_('hello'),
				'B' => new B3(),
			]);
		});
	}

}

class B3 extends Component {

	public function __construct() {
		$this->mount(function () {
			$this->setTemplateContent('<b>{HELLO/} {"world"} {C}</b>');
			$this->setVars([
				'HELLO' => $this->_('hello'),
				'C' => new C3(),
			]);
		});
	}

}

class C3 extends Component {

	public function __construct() {
		$this->mount(function () {
			$this->addTranslator(__DIR__ . '/lang/alt', 'php', 'fr');
			$this->setTemplateContent('<c>{HELLO/} {"world"}</c>');
			$this->setVars([
				'HELLO' => $this->_('hello'),
			]);
		});
	}

}
#endregion

class MyComponent extends Component {

	public function __construct() {
		parent::__construct();
		$this->setTemplateFile(__DIR__ . '/tpl/mycomponent.tpl');

		// Add a translator, it will look for translation file into specified directory
		$this->addTranslator(__DIR__ . '/lang', 'php', 'fr');

		// Use translation method
		$this->setVar('SLOT1', $this->_('Hello world'));
		$this->setVar('SLOT2', $this->_('This is %s', 'an apple'));
		$this->setVar('SLOT3', $this->_('This is %s', 'an pineapple'));
		$this->setVar('SLOT4', $this->_('Number of %d max', 10));
	}

}

class TranslatorTest extends TestCase {

	protected static function getMethod(string $name) {
		$class = new ReflectionClass('\Sy\Component');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	public function testTranslate() {
		$a = new Component();
		$a->addTranslator(__DIR__ . '/lang', 'php', 'fr');
		$translate = self::getMethod('_');
		$this->assertEquals($translate->invokeArgs($a, ['Hello world']), 'Bonjour monde');
		$this->assertEquals($translate->invokeArgs($a, ['This is %s', 'an apple']), 'Ceci est une pomme');
		$this->assertEquals($translate->invokeArgs($a, ['This is %s']), 'Ceci est %s');
		$this->assertEquals($translate->invokeArgs($a, ['Number of %d max', 10]), 'Nombre de 10 max');
		$this->assertEquals(sprintf($translate->invokeArgs($a, ['Number of %d max']), 10), 'Nombre de 10 max');
	}

	public function testMultiTranslatorsOrder1() {
		$a = new Component();
		$a->addTranslator(__DIR__ . '/lang/alt', 'php', 'fr');
		$a->addTranslator(__DIR__ . '/lang', 'php', 'fr');
		$translate = self::getMethod('_');
		$this->assertEquals($translate->invokeArgs($a, ['I am the component %s', 'A']), 'Je suis le composant A');
		$this->assertEquals($translate->invokeArgs($a, ['Hello world']), 'Bonjour monde');
		$this->assertEquals($translate->invokeArgs($a, ['This is %s', 'an apple']), 'Ceci est une pomme');
		$this->assertEquals($translate->invokeArgs($a, ['This is %s']), 'Ceci est %s');
		$this->assertEquals($translate->invokeArgs($a, ['Number of %d max', 10]), 'Nombre de 10 max');
		$this->assertEquals(sprintf($translate->invokeArgs($a, ['Number of %d max']), 10), 'Nombre de 10 max');
	}

	public function testMultiTranslatorsOrder2() {
		$a = new Component();
		$a->addTranslator(__DIR__ . '/lang', 'php', 'fr');
		$a->addTranslator(__DIR__ . '/lang/alt', 'php', 'fr');
		$translate = self::getMethod('_');
		$this->assertEquals($translate->invokeArgs($a, ['I am the component %s', 'A']), 'Je suis le composant A');
		$this->assertEquals($translate->invokeArgs($a, ['Hello world']), 'Bonjour monde');
		$this->assertEquals($translate->invokeArgs($a, ['This is %s', 'an apple']), "C'est une pomme");
		$this->assertEquals($translate->invokeArgs($a, ['This is %s']), "C'est %s");
		$this->assertEquals($translate->invokeArgs($a, ['Number of %d max', 10]), 'Le nombre maximum est de 10');
		$this->assertEquals(sprintf($translate->invokeArgs($a, ['Number of %d max']), 10), 'Le nombre maximum est de 10');
	}

	public function testMergeTranslators() {
		$a = new Component();
		$a->addTranslator(__DIR__ . '/lang', 'php', 'fr');
		$b = new Component();
		$a->setVar('SLOT', $b);
		$translate = self::getMethod('_');
		$this->assertEquals($translate->invokeArgs($b, ['Hello world']), 'Bonjour monde');
		$this->assertEquals($translate->invokeArgs($b, ['This is %s', 'an apple']), 'Ceci est une pomme');
		$this->assertEquals($translate->invokeArgs($b, ['This is %s']), 'Ceci est %s');
		$this->assertEquals($translate->invokeArgs($b, ['Number of %d max', 10]), 'Nombre de 10 max');
		$this->assertEquals(sprintf($translate->invokeArgs($b, ['Number of %d max']), 10), 'Nombre de 10 max');
	}

	public function testMergeTranslatorsOrder1() {
		$a = new Component();
		$a->addTranslator(__DIR__ . '/lang', 'php', 'fr');
		$b = new Component();
		$a->setVar('SLOT', $b);
		$b->addTranslator(__DIR__ . '/lang/alt', 'php', 'fr');
		$translate = self::getMethod('_');
		$this->assertEquals($translate->invokeArgs($b, ['I am the component %s', 'B']), 'Je suis le composant B');
		$this->assertEquals($translate->invokeArgs($b, ['Hello world']), 'Bonjour monde');
		$this->assertEquals($translate->invokeArgs($b, ['This is %s', 'an apple']), "C'est une pomme");
		$this->assertEquals($translate->invokeArgs($b, ['This is %s']), "C'est %s");
		$this->assertEquals($translate->invokeArgs($b, ['Number of %d max', 10]), 'Le nombre maximum est de 10');
		$this->assertEquals(sprintf($translate->invokeArgs($b, ['Number of %d max']), 10), 'Le nombre maximum est de 10');
	}

	public function testMergeTranslatorsOrder2() {
		$a = new Component();
		$a->addTranslator(__DIR__ . '/lang', 'php', 'fr');
		$b = new Component();
		$b->addTranslator(__DIR__ . '/lang/alt', 'php', 'fr');
		$a->setVar('SLOT', $b);
		$translate = self::getMethod('_');
		$this->assertEquals($translate->invokeArgs($b, ['I am the component %s', 'B']), 'Je suis le composant B');
		$this->assertEquals($translate->invokeArgs($b, ['Hello world']), 'Bonjour monde');
		$this->assertEquals($translate->invokeArgs($b, ['This is %s', 'an apple']), "C'est une pomme");
		$this->assertEquals($translate->invokeArgs($b, ['This is %s']), "C'est %s");
		$this->assertEquals($translate->invokeArgs($b, ['Number of %d max', 10]), 'Le nombre maximum est de 10');
		$this->assertEquals(sprintf($translate->invokeArgs($b, ['Number of %d max']), 10), 'Le nombre maximum est de 10');
	}

	public function testComposition0() {
		$p = new P();
		$this->assertEquals('<a>bonjour monde <b>bonjour monde <c0>bonjour monde</c><c1>bonjour monde</c><c2>bonjour monde</c></b></a>', $p->render());
	}

	public function testComposition1() {
		$p = new P1();
		$this->assertEquals('<a>bonjour monde <b>bonjour monde <c0>bonjour monde</c><c1>bonjour monde</c><c2>bonjour monde</c></b></a>', $p->render());
	}

	public function testComposition2() {
		$p = new P2();
		$this->assertEquals('<a>bonjour monde <b>bonjour monde <c0>bonjour monde</c><c1>bonjour monde</c><c2>bonjour monde</c></b></a>', $p->render());
	}

	public function testComposition3() {
		$a = new A3();
		$this->assertEquals('<a>bonjour monde <b>bonjour monde <c>salut monde</c></b></a>', $a->render());
	}

	public function testMyComponent() {
		$c = new MyComponent();
		$this->assertEquals(file_get_contents(__DIR__ . '/result/mycomponent.txt'), $c->render());
	}

}