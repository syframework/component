# sy/component

The concept of **component** allows you to build an application as a tree of simpler components.

A component is a package of several elements:
* classes
* templates
* resources (images, css, js etc...)

A component can be used alone or in another component.

**Sy\Component** is the base class of all components.

## Component template engine

The template engine used is [sy/template](https://github.com/syframework/template)

### Template syntax notion: var and block

Example with **setVar** method, your PHP script:
```php
<?php

$c = new Sy\Component();
$c->setTemplateFile(__DIR__ . '/template.tpl');
$c->setVar('NAME', 'World');

echo $c;
```

Template file, template.tpl:
```html
Hello {NAME}
```

Output result:
```html
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

When a block is never parsed, you can use the ELSE block to show a default content.

```html
<!-- BEGIN MY_BLOCK -->
Block content
<!-- ELSE MY_BLOCK -->
Block not parsed
<!-- END MY_BLOCK -->
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
```html
Hello <?php echo $NAME ?>
```

Output result:
```html
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
		parent::__construct();
		$this->setTemplateFile(__DIR__ . '/Hello.tpl');
		$this->setVar('NAME', $name);
	}

}
```

Hello.tpl
```html
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

Use **setComponent** method to add a component in another one.

```php
<?php

$c = new Sy\Component();
$c->setTemplateFile(__DIR__ . '/template.tpl');
$c->setComponent('NAME', new Hello());
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