<?php

use PHPUnit\Framework\TestCase;
use Sy\Component;

class MyTestComponent extends Component {

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @param string $id
	 */
	public function __construct($id) {
		parent::__construct();
		$this->id = $id;
		$this->mount(function () {
			$method = $this->id;
			$this->$method();
		});
	}

	public function blocks() {
		$this->setTemplateFile(__DIR__ . '/tpl/' . $this->id . '.tpl');
		$this->setBlocks('foo', [
			['firstname' => 'John', 'lastname' => 'Doe', 'age' => 32],
			['firstname' => 'John', 'lastname' => 'Wick', 'age' => 42],
			['firstname' => 'Bob', 'lastname' => 'Doe'],
			['firstname' => 'Jane', 'lastname' => 'Doe', 'age' => 25],
		]);
	}

	public function block() {
		$this->setTemplateFile(__DIR__ . '/tpl/' . $this->id . '.tpl');
		$this->setBlock('BAZ');
		$this->setBlock('BAR');
		$this->setBlock('FOO');
	}

	public function block_loop() {
		$this->setTemplateFile(__DIR__ . '/tpl/' . $this->id . '.tpl');
		for ($i = 0; $i < 3; $i++) {
			for ($j = 0; $j < 3; $j++) {
				for ($k = 0; $k < 3; $k++) {
					$this->setBlock('C', ['C_ID' => "C $i $j $k"]);
				}
				$this->setBlock('B', ['B_ID' => "B $i $j"]);
			}
			$this->setBlock('A', ['A_ID' => "A $i"]);
		}
	}

	public function block_component1() {
		$this->setTemplateFile(__DIR__ . '/tpl/block_component.tpl');

		$a = new Component();
		$a->setTemplateContent('<a>I am A</a>');
		$b = new Component();
		$b->setTemplateContent('<b>I am B</b>');
		$c = new Component();
		$c->setTemplateContent('<c>I am C</c>');

		$this->setVar('SLOT', $a);
		$this->setBlock('BLOCK');
		$this->setVar('SLOT', $b);
		$this->setBlock('BLOCK');
		$this->setBlock('BLOCK');
	}

	public function block_component2() {
		$this->setTemplateFile(__DIR__ . '/tpl/block_component.tpl');

		$a = new Component();
		$a->setTemplateContent('<a>I am A</a>');
		$b = new Component();
		$b->setTemplateContent('<b>I am B</b>');
		$c = new Component();
		$c->setTemplateContent('<c>I am C</c>');

		$this->setVar('SLOT', $a);
		$this->setBlock('BLOCK');
		$this->setBlock('BLOCK');
		$this->setBlock('BLOCK');
	}

	public function block_component3() {
		$this->setTemplateFile(__DIR__ . '/tpl/block_component.tpl');

		$a = new Component();
		$a->setTemplateContent('<a>I am A</a>');
		$b = new Component();
		$b->setTemplateContent('<b>I am B</b>');
		$c = new Component();
		$c->setTemplateContent('<c>I am C</c>');

		$this->setVar('SLOT', $a);
		$this->setBlock('BLOCK');
		$this->setVar('SLOT', $b);
		$this->setBlock('BLOCK');
		$this->setVar('SLOT', $c);
		$this->setBlock('BLOCK');
	}

	public function block_component4() {
		$this->setTemplateFile(__DIR__ . '/tpl/block_component.tpl');

		$a = new Component();
		$a->setTemplateContent('<a>I am A</a>');
		$b = new Component();
		$b->setTemplateContent('<b>I am B</b>');
		$c = new Component();
		$c->setTemplateContent('<c>I am C</c>');

		$this->setVar('SLOT', $a);
		$this->setBlock('BLOCK', ['SLOT' => $b]);
		$this->setBlock('BLOCK', ['SLOT' => $c]);
	}

	public function append_component() {
		$this->setTemplateContent('{SLOT}');

		$a = new Component();
		$a->setTemplateContent('<a>I am A</a>');
		$b = new Component();
		$b->setTemplateContent('<b>I am B</b>');
		$c = new Component();
		$c->setTemplateContent('<c>I am C</c>');

		$this->setVar('SLOT', $a);
		$this->setVar('SLOT', 'foo', true);
		$this->setVar('SLOT', $b, true);
		$this->setVar('SLOT', 'bar', true);
		$this->setVar('SLOT', $c, true);
		$this->setVar('SLOT', 'baz', true);
	}

}

class ComponentTest extends TestCase {

	public function assertFileContentEqualsComponentRender(string $filename, Component $component) {
		$minify = function ($code) {
			$search = array("\t", "\r", "\n");
			$code = str_replace($search, ' ', $code);
			$code = preg_replace('/\s+/', ' ', $code);
			return trim($code);
		};
		$this->assertEquals($minify(file_get_contents($filename)), $minify($component->render()));
	}

	public function testSetTemplateType() {
		$c = new Component();
		$c->setTemplateContent('<?php echo $SLOT ?>');
		$c->setTemplateType('php');
		$c->setVar('SLOT', 'hello world');
		$this->assertEquals('php', $c->getTemplateType());
	}

	public function testSetTemplateFileNotFound() {
		$c = new Component();
		$c->setTemplateFile(__DIR__ . '/not_found.tpl');
		$this->assertEquals('', strval($c));
	}

	public function testGetParent() {
		$a = new Component();
		$b = new Component();
		$a->setComponent('SLOT', $b);
		$this->assertEquals($a, $b->getParent());
	}

	public function testSetVarAppend() {
		$a = new Component();
		$a->setTemplateContent('{SLOT}');
		$a->setVar('SLOT', 'hello');
		$a->setVar('SLOT', 'world', true);
		$this->assertEquals('helloworld', strval($a));
	}

	public function testSetBlock() {
		$id = 'block';
		$c = new MyTestComponent($id);
		$this->assertFileContentEqualsComponentRender(__DIR__ . "/result/$id.txt", $c);
	}

	public function testSetBlocks() {
		$id = 'blocks';
		$c = new MyTestComponent($id);
		$this->assertFileContentEqualsComponentRender(__DIR__ . "/result/$id.txt", $c);
	}

	public function testSetBlockLoop() {
		$id = 'block_loop';
		$c = new MyTestComponent($id);
		$this->assertFileContentEqualsComponentRender(__DIR__ . "/result/$id.txt", $c);
	}

	public function testSetBlockComponent() {
		for ($i = 1; $i <= 4; $i++) {
			$id = 'block_component' . $i;
			$c = new MyTestComponent($id);
			$this->assertFileContentEqualsComponentRender(__DIR__ . "/result/$id.txt", $c);
		}
	}

	public function testAppendComponent() {
		$id = 'append_component';
		$c = new MyTestComponent($id);
		$this->assertFileContentEqualsComponentRender(__DIR__ . "/result/$id.txt", $c);
	}

	public function testSetBlockWithVars() {
		$c = new Component();
		$c->setTemplateContent('<!-- BEGIN A -->{SLOT}<!-- END A -->');
		$c->setVar('SLOT', 'Hello');
		foreach (['Foo', 'Bar', 'Baz'] as $v) {
			$c->setBlock('A', ['SLOT' => $v]);
		}
		$c->setBlock('A');
		$this->assertEquals('FooBarBazHello', $c->render());
	}

	public function testConcat() {
		$a = new Component();
		$a->setTemplateContent('<a>');
		$b = new Component();
		$b->setTemplateContent('<b>');
		$this->assertEquals("<a>\n<b>", strval(Component::concat($a, $b)));
		$this->assertEquals('', strval(Component::concat(...[])));
	}

	public function testLifecycle() {
		$this->expectOutputString('B Added' . PHP_EOL . 'A Added' . PHP_EOL . 'A Mount' . PHP_EOL . 'B Mount' . PHP_EOL . 'B Mounted' . PHP_EOL . 'A Mounted' . PHP_EOL);

		$a = new Component();
		$a->mounted(function () {
			echo 'A Mounted' . PHP_EOL;
		});
		$a->mount(function () {
			echo 'A Mount' . PHP_EOL;
		});
		$a->added(function () {
			echo 'A Added' . PHP_EOL;
		});

		$b = new Component();
		$b->mounted(function () {
			echo 'B Mounted' . PHP_EOL;
		});
		$b->mount(function () {
			echo 'B Mount' . PHP_EOL;
		});
		$b->added(function () {
			echo 'B Added' . PHP_EOL;
		});

		$a->setVar('SLOT', $b); // B is added into A
		echo $a; // Render A
	}

}