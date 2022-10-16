<?php

use PHPUnit\Framework\TestCase;
use Sy\Component;

class ActionTestComponent extends Component {

	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @param string $action
	 */
	public function __construct($action = null) {
		parent::__construct();
		if (!empty($action)) {
			$_REQUEST['action'] = $action;
		}
		$this->actionDispatch('action', 'default');
	}

	public function getValue() {
		return $this->value;
	}

	protected function defaultAction() {
		$this->value = 'default action';
	}

	protected function doThisNumberOneAction() {
		$this->value = 'do this number 1 action';
	}

	protected function doThisNumberTwoAction() {
		$this->value = 'do this number 2 action';
	}

}

class ActionTest extends TestCase {

	public function testDefaultAction() {
		$c = new ActionTestComponent();
		$this->assertEquals('default action', $c->getValue());
		$c = new ActionTestComponent('foo');
		$this->assertEquals('default action', $c->getValue());
	}

	public function testAction1() {
		$a = new ActionTestComponent('doThisNumberOne');
		$this->assertEquals('do this number 1 action', $a->getValue());
		$b = new ActionTestComponent('do_this_number_one');
		$this->assertEquals('do this number 1 action', $b->getValue());
		$c = new ActionTestComponent('do-this-number-one');
		$this->assertEquals('do this number 1 action', $c->getValue());
	}

	public function testAction2() {
		$a = new ActionTestComponent('doThisNumberTwo');
		$this->assertEquals('do this number 2 action', $a->getValue());
		$b = new ActionTestComponent('do_this_number_two');
		$this->assertEquals('do this number 2 action', $b->getValue());
		$c = new ActionTestComponent('do-this-number-two');
		$this->assertEquals('do this number 2 action', $c->getValue());
	}

}