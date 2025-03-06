# sy/component

The concept of **component** allows you to build an application as a tree of simpler components (Composite design pattern). And each component can be reusable.

Basically we can assume that a component is a stringable object. At this point, it just use a template to generate a string that can be in any format (html, xml, json, plain text etc...). On top of this, we can build web components by adding css and js properties.

The class **Sy\Component** is the base class of other **web components** ([sy/webcomponent](https://github.com/syframework/webcomponent)) and **html page and elements** ([sy/html](https://github.com/syframework/html)) like form and table etc...

## Component template engine

The template engine used is [sy/template](https://github.com/syframework/template)

### Template syntax notion: **slot** and **block**

Example with **setVar** method for filling a slot, your PHP script:
```php
<?php

$c = new Sy\Component();
$c->setTemplateFile(__DIR__ . '/template.tpl');
$c->setVar('NAME', 'World');

echo $c;
```

Template file, template.tpl:
```
Hello {NAME}
```

Output result:
```
Hello World
```

Example with **setBlock** method, your PHP script:
```php
<?php

$c = new Sy\Component();
$c->setTemplateFile(__DIR__ . '/template.tpl');

for ($i = 0; $i < 10; $i++) {
	$c->setVar('VALUE', $i);
	$c->setBlock('MY_BLOCK');
}

echo $c;
```

Template file, template.tpl:
```html
<!-- BEGIN MY_BLOCK -->
Block {VALUE}
<!-- END MY_BLOCK -->
```

Output result:
```
Block 0
Block 1
Block 2
Block 3
Block 4
Block 5
Block 6
Block 7
Block 8
Block 9
```

### ELSE block

When a block is not set, you can use the ELSE block to show a default content.

```html
<!-- BEGIN MY_BLOCK -->
Block content
<!-- ELSE MY_BLOCK -->
Block not parsed
<!-- END MY_BLOCK -->
```

### Set block with isolated vars

By default, when the second parameter of setBlock method is empty, it will use vars globally set for setting slots in the block scope.
It is possible to use isolated vars for setting slots on the block scope with the second parameter.

```php
<?php

$c = new Sy\Component();
$c->setTemplateContent('<!-- BEGIN A -->{SLOT}<!-- END A -->');

$c->setVar('SLOT', 'Hello');

foreach (['Foo', 'Bar', 'Baz'] as $v) {
	$c->setBlock('A', ['SLOT' => $v]);
}

$c->setBlock('A');

echo $c;
```

Result:
```
FooBarBazHello
```

### Set blocks using a data array

```php
class A extends Sy\Component {

	public function __construct() {
		parent::__construct();

		$this->setTemplateFile(__DIR__ . '/template.html');

		// setBlocks will set a block for each line in the data array
		$this->setBlocks('foo', [
			['firstname' => 'John', 'lastname' => 'Doe', 'age' => 32],
			['firstname' => 'John', 'lastname' => 'Wick', 'age' => 42],
			['firstname' => 'Jane', 'lastname' => 'Doe', 'age' => 25],
			['firstname' => 'Bob', 'lastname' => 'Doe'],
		]);
	}

}

echo new A();
```

Template file, template.html:
```html
Nb persons: {FOO_COUNT}
<!-- BEGIN FOO_BLOCK -->
<div>
	Index: {FOO_INDEX}
	Firstname: {FOO_FIRSTNAME}
	Lastname: {FOO_LASTNAME}
	<!-- BEGIN FOO_AGE_BLOCK -->
	Age: {FOO_AGE}
	<!-- ELSE FOO_AGE_BLOCK -->
	Unknown age
	<!-- END FOO_AGE_BLOCK -->
</div>
<!-- END FOO_BLOCK -->
```

Output:
```
Nb persons: 3

<div>
	Index: 1
	Firstname: John
	Lastname: Doe
	Age: 32
</div>
<div>
	Index: 2
	Firstname: John
	Lastname: Wick
	Age: 42
</div>
<div>
	Index: 3
	Firstname: Jane
	Lastname: Doe
	Age: 25
</div>
<div>
	Index: 4
	Firstname: Bob
	Lastname: Doe
	Unknown age
</div>
```

### Alternative template syntax

It's possible to use simple PHP template syntax.
You must specify that you are using a PHP template file:
```php
<?php

$c = new Sy\Component();

// use a php template file with the second parameter
$c->setTemplateFile(__DIR__ . '/template.tpl', 'php');
$c->setVar('NAME', 'World');

echo $c;
```

PHP template file, template.tpl:
```
Hello <?php echo $NAME ?>
```

Output result:
```
Hello World
```

## Create a component

Create a custom class derived from Sy\Component class.

For example in Hello.php

```php
<?php
use Sy\Component;

class Hello extends Component {

	public function  __construct($name = 'world') {
		$this->setTemplateFile(__DIR__ . '/Hello.tpl');
		$this->setVar('NAME', $name);
	}

}
```

Hello.tpl
```
Hello {NAME}!
```

Use your component:

```php
<?php

// echo 'Hello world!'
$hello = new Hello();
echo $hello;

// echo 'Hello toto!'
$hello = new Hello('toto');
echo $hello;
```

## Add a component in another one

Use **setVar** method to add a component in another one.

```php
<?php

$c = new Sy\Component();
$c->setTemplateFile(__DIR__ . '/template.tpl');
$c->setVar('NAME', new Hello());
```

## Component actions

The **actionDispatch** method help you to call action method.

This method takes 2 arguments:
- actionName: $_REQUEST variable name, index.php?**action**=foo
- defaultMethod: this one is optionnal, if no action is called, it will perform this method

An action method name must be suffixed by 'Action': fooAction

For example in MyComponent.php
```php
<?php
use Sy\Component;

class MyComponent extends Component {

	public function  __construct() {
		parent::__construct();
		$this->setTemplateFile(__DIR__ . '/MyComponent.tpl');

		// if $_REQUEST['action'] is not set, call initAction
		$this->actionDispatch('action', 'init');
	}

	public function initAction() {

	}

	public function fooAction() {

	}

}
```

## Component translators

Translator can be added in a Component.
Each Translator will load translation data from a file in a specified directory.
This translation file must be named as the detected language. For example, if the detected language is "fr",
the PHP Translator will try to load "fr.php". And Gettext Translator will try to load "fr.mo".

This feature is provided by the library [sy/translate](https://github.com/syframework/translate)

### Language detection

Language will be detected using these variables in this order:

1. $_SESSION['sy_language']
2. $_COOKIE['sy_language']
3. $_SERVER['HTTP_ACCEPT_LANGUAGE']

### Translation methods

- void **Component::addTranslator**(string *$directory* [, string *$type* = 'php', string *$lang* = ''])
- string **Component::_**(mixed *$values*)

Exemple:
```php
<?php

use Sy\Component;

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

echo new MyComponent();
```

PHP Translation file:
```php
<?php
return array(
	'Hello world' => 'Bonjour monde',
	'This is %s' => 'Ceci est %s',
	'an apple' => 'une pomme',
	'a pineapple' => 'un ananas',
	'Number of %d max' => 'Nombre de %d max',
);
```

Template file:
```
{"Hello world"}

{"No traduction"}

{SLOT1}
{SLOT2}
{SLOT3}
{SLOT4}
```

Output result:
```
Bonjour monde

No traduction

Bonjour monde
Ceci est une pomme
Ceci est an pineapple
Nombre de 10 max
```

### Add multiple translators

It's possible to add multiple translators in a component. The order of addition is important because the translate process will stop right after the first translation data found.

### Translators transmission to inner web component

When adding a web component B in a web component A, all the translators of A will be added into B.

```php
<?php

use Sy\Component;

class A extends Component {

	public function __construct() {
		$this->addTranslator(__DIR__ . '/lang', 'php', 'fr');
		$this->setTemplateContent('<a>{HELLO/} {"world"} {B}</a>');

		$this->mount(function () {
			$this->setVars([
				'HELLO' => $this->_('hello'),
				'B' => new B()
			]);
		});
	}

}

class B extends Component {

	public function __construct() {
		$this->setTemplateContent('<b>{HELLO/} {"world"} {C}</b>');

		$this->mount(function () {
			$this->setVars([
				'HELLO' => $this->_('hello'),
				'C' => new C()
			]);
		});
	}

}

class C extends Component {

	public function __construct() {
		$this->addTranslator(__DIR__ . '/lang/alt', 'php', 'fr');
		$this->setTemplateContent('<c>{HELLO/} {"world"}</c>');

		$this->mount(function () {
			$this->setVars([
				'HELLO' => $this->_('hello'),
			]);
		});
	}

}

echo new A();
```

Translation file added in component A:
```php
return array(
	'hello' => 'bonjour',
	'world' => 'monde',
);
```

Translation file added in component C:
```php
return array(
	'hello' => 'salut',
);
```

Output result:
```
<a>bonjour monde <b>bonjour monde <c>salut monde</c></b></a>
```

The translator of A is transmitted to B and C. C will use his own translator in priority.

The translation method (underscore) must be called during the **mount** stage. Please see the [component lifecyle](https://github.com/syframework/component/wiki/Component-lifecycle) to know more about every events triggered during a component's rendering.

We need to use the **mount** method here to register callbacks. Theses callbacks are called on the mount event. This is because we need to ensure that all the leaf components received the translators from their parents components.