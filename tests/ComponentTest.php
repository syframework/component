<?php

use PHPUnit\Framework\TestCase;
use Sy\Component;

class MyTestComponent extends Component {

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @param string $id
	 */
	public function __construct($id) {
		parent::__construct();
		$this->id = $id;
		$this->setTemplateFile(__DIR__ . '/tpl/' . $this->id . '.tpl');
		$this->mount(function () {
			$method = $this->id;
			$this->$method();
		});
	}

	public function blocks() {
		$this->setBlocks('foo', [
			['firstname' => 'John', 'lastname' => 'Doe', 'age' => 32],
			['firstname' => 'John', 'lastname' => 'Wick', 'age' => 42],
			['firstname' => 'Bob', 'lastname' => 'Doe'],
			['firstname' => 'Jane', 'lastname' => 'Doe', 'age' => 25],
		]);
	}

	public function block() {
		$this->setBlock('BAZ');
		$this->setBlock('BAR');
		$this->setBlock('FOO');
	}

	public function block_loop() {
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

}

class ComponentTest extends TestCase {

	public function testSetBlock() {
		$c = new MyTestComponent('block');
		$this->assertEquals(file_get_contents(__DIR__ . '/result/block.txt'), $c->render());
	}

	public function testSetBlocks() {
		$c = new MyTestComponent('blocks');
		$this->assertEquals(file_get_contents(__DIR__ . '/result/blocks.txt'), $c->render());
	}

	public function testSetBlockLoop() {
		$c = new MyTestComponent('block_loop');
		$this->assertEquals(file_get_contents(__DIR__ . '/result/block_loop.txt'), $c->render());
	}

}