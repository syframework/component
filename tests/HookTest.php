<?php

use PHPUnit\Framework\TestCase;
use Sy\Component;

class HookTest extends TestCase {

	public function testOneComponentHookOrder() {
		$a = new Component();
		$a->mounted(function () {
			echo '3';
		});
		$a->mount(function () {
			echo '2';
		});
		$a->added(function () {
			echo '1';
		});
		$this->expectOutputString('123');
		echo $a;
	}

	public function testTwoComponentsHookOrder() {
		$a = new Component();
		$a->mounted(function () {
			echo '6';
		});
		$a->mount(function () {
			echo '3';
		});
		$a->added(function () {
			echo '2';
		});
		$b = new Component();
		$b->mounted(function () {
			echo '5';
		});
		$b->mount(function () {
			echo '4';
		});
		$b->added(function () {
			echo '1';
		});
		$a->setVar('FOO', $b);
		$this->expectOutputString('123456');
		echo $a;
	}

	public function testThreeComponentsHookOrder() {
		$a = new Component();
		$a->mounted(function () {
			echo '9';
		});
		$a->mount(function () {
			echo '4';
		});
		$a->added(function () {
			echo '3';
		});
		$b = new Component();
		$b->mounted(function () {
			echo '8';
		});
		$b->mount(function () {
			echo '5';
		});
		$b->added(function () {
			echo '2';
		});
		$c = new Component();
		$c->mounted(function () {
			echo '7';
		});
		$c->mount(function () {
			echo '6';
		});
		$c->added(function () {
			echo '1';
		});
		$b->setVar('FOO', $c);
		$a->setVar('FOO', $b);
		$this->expectOutputString('123456789');
		echo $a;
	}

	public function testDoubleComponentsHookOrder() {
		$a = new Component();
		$a->mounted(function () {
			echo '6';
		});
		$a->mount(function () {
			echo '3';
		});
		$a->added(function () {
			echo '2';
		});
		$b = new Component();
		$b->mounted(function () {
			echo '5';
		});
		$b->mount(function () {
			echo '4';
		});
		$b->added(function () {
			echo '1';
		});
		$a->setVar('FOO', $b);
		$a->setVar('BAR', $b, true);
		$this->expectOutputString('123456');
		echo $a;
	}

}