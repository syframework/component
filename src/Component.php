<?php
namespace Sy;

use Sy\Template\TemplateProvider,
	Sy\Template\TemplateFileNotFoundException,
	Sy\Translate\TranslatorProvider,
	Sy\Debug\Debugger;

class Component {

	/**
	 * Template engine
	 *
	 * @var \Sy\Template\ITemplate
	 */
	private $template;

	/**
	 * Template type
	 *
	 * @var string
	 */
	private $templateType = '';

	/**
	 * Translators
	 *
	 * @var \Sy\Translate\ITranslator[] Array of translators
	 */
	private $translators = array();

	/**
	 * @var array
	 */
	private $vars = array();

	/**
	 * @var array
	 */
	private $blocks = array();

	/**
	 * @var string
	 */
	private $render;

	/**
	 * @var callable[] Callbacks invoked on mount event
	 */
	private $mount = array();

	/**
	 * @var callable[] Callbacks invoked on mounted event
	 */
	private $mounted = array();

	/**
	 * Parent component
	 *
	 * @var Component|null
	 */
	private $parent = null;

	public function __construct() {}

	/**
	 * @return \Sy\Template\ITemplate
	 */
	private function getTemplate() {
		if (!isset($this->template)) {
			$this->template = TemplateProvider::createTemplate($this->getTemplateType());
		}
		return $this->template;
	}

	/**
	 * Return template type
	 *
	 * @return string
	 */
	public function getTemplateType() {
		return $this->templateType;
	}

	/**
	 * Set the template type
	 *
	 * @param string $type
	 */
	public function setTemplateType($type = '') {
		if ($this->templateType === $type) return;
		$this->templateType = $type;
		$this->template = TemplateProvider::createTemplate($type);
	}

	/**
	 * Set the main template file
	 *
	 * @param string $file
	 * @param string $type Template type
	 */
	public function setTemplateFile($file, $type = '') {
		$this->setTemplateType($type);
		try {
			$this->getTemplate()->setFile($file);
		} catch (TemplateFileNotFoundException $e) {
			$this->setTemplateContent('');
			$info = $this->getDebugTrace();
			$info['type'] = 'Template';
			$this->logError($e->getMessage(), $info);
		}
	}

	/**
	 * Set the component template content
	 *
	 * @param string $content Template content
	 * @param string $type Template type
	 */
	public function setTemplateContent($content, $type = '') {
		$this->setTemplateType($type);
		$this->getTemplate()->setContent($content);
	}

	/**
	 * Return the parent component
	 *
	 * @return Component
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Set the component parent component
	 *
	 * @param Component $component
	 */
	public function setParent(Component $component) {
		$this->parent = $component;
	}

	/**
	 * Set a value of a variable
	 *
	 * @param string $var
	 * @param string|Component $value
	 * @param bool $append
	 */
	public function setVar($var, $value, $append = false) {
		if ($value instanceof Component) {
			$value->addTranslators($this->getTranslators());
			$component = ($append and !empty($this->vars[$var])) ? static::concat($this->vars[$var], $value) : $value;
			$component->setParent($this);
			$this->vars[$var] = $component;
			return;
		}
		if ($append and !empty($this->vars[$var])) {
			if ($this->vars[$var] instanceof Component) {
				$value = static::concat($this->vars[$var], $value);
				$value->setParent($this);
			} else {
				$value = $this->vars[$var] . $value;
			}
		}
		$this->vars[$var] = $value;
	}

	/**
	 * Set an array of values
	 *
	 * @param array $values associative array var => value
	 */
	public function setVars(array $values) {
		foreach ($values as $var => $value) {
			$this->setVar($var, $value);
		}
	}

	/**
	 * Set a block
	 *
	 * @param string $block Block name
	 * @param array $vars Block variables, if empty use variables set in the component
	 */
	public function setBlock($block, array $vars = array()) {
		foreach ($vars as $k => $v) {
			if ($v instanceof Component) $v->setParent($this);
		}
		$this->blocks[][$block] = empty($vars) ? $this->vars : $vars;
	}

	/**
	 * Set multiple blocks using a data array
	 *
	 * @param string $name Block name
	 * @param array $data Array of associative array, example:
	 * [
	 *   ['k1' => '...', 'k2' => '...', 'k3' => '...'],
	 *   ['k1' => '...', 'k2' => '...', 'k3' => '...'],
	 *   ...
	 * ]
	 *
	 * Slot names will be:
	 * -Variables slots: {$name_K1}, {$name_K2}, {$name_K3}...
	 * -Iteration counter: {$name_INDEX}
	 * -Block name: {$name_BLOCK}
	 * -Data count: {$name_COUNT}
	 */
	public function setBlocks($name, array $data) {
		$name = strtoupper($name);
		$this->setVar($name . '_COUNT', count($data));
		foreach ($data as $i => $row) {
			$vars = array_combine(array_map(function ($v) use ($name) { return $name . '_' . strtoupper($v); }, array_keys($row)), $row);
			$vars[$name . '_INDEX'] = $i + 1;
			foreach ($row as $k => $v) {
				if (!empty($v)) $this->setBlock($name . '_' . strtoupper($k) . '_BLOCK', array($name . '_' . strtoupper($k) => $v));
			}
			$this->setBlock($name . '_BLOCK', $vars);
		}
	}

	/**
	 * Add a component
	 *
	 * @param string $where
	 * @param Component $component
	 * @param boolean $append
	 */
	public function setComponent($where, Component $component, $append = false) {
		$this->setVar($where, $component, $append);
	}

	/**
	 * Add a Translator
	 *
	 * @param string $directory Translator directory
	 * @param string $type Translator type
	 * @param string $lang Translation language. Use auto detection by default
	 */
	public function addTranslator($directory, $type = 'php', $lang = '') {
		array_unshift($this->translators, TranslatorProvider::createTranslator($directory, $type, $lang));
	}

	/**
	 * @param \Sy\Translate\ITranslator[] $translators Array of ITranslator
	 */
	public function addTranslators(array $translators) {
		$this->translators = array_merge($this->translators, $translators);
	}

	/**
	 * @return \Sy\Translate\ITranslator[] Array of ITranslator
	 */
	public function getTranslators() {
		return $this->translators;
	}

	/**
	 * @param \Sy\Translate\ITranslator[] $translators Array of ITranslator
	 */
	public function setTranslators(array $translators) {
		$this->translators = $translators;
	}

	/**
	 * Translate message
	 *
	 * @param mixed $values The first argument can be a sprintf format string and others arguments will be used as sprintf values
	 * @return string
	 */
	public function _(...$values) {
		// Can also accept a single array as argument
		if (count($values) === 1 and is_array($values[0])) $values = $values[0];

		$message = array_shift($values);

		foreach ($this->translators as $translator) {
			$res = $translator->translate($message);
			if (!empty($res)) break;
		}

		array_walk($values, function(&$value) {
			foreach ($this->translators as $translator) {
				$a = $translator->translate($value);
				if (!empty($a)) {
					$value = $a;
					break;
				}
			}
		});

		if (empty($res)) $res = $message;

		return empty($values) ? $res : sprintf($res, ...$values);
	}

	/**
	 * Add a callback on mount event
	 *
	 * @param callable $callback
	 */
	public function mount(callable $callback) {
		$this->mount[] = $callback;
	}

	/**
	 * Add a callback on mounted event
	 *
	 * @param callable $callback
	 */
	public function mounted(callable $callback) {
		$this->mounted[] = $callback;
	}

	/**
	 * Return the render of the component
	 *
	 * @return string
	 */
	public function __toString() {
		if (isset($this->render)) return $this->render;
		return $this->render();
	}

	/**
	 * Return the component render
	 */
	public function render() {
		if (isset($this->render)) return $this->render;

		// Mount event
		foreach ($this->mount as $mount) {
			$mount();
		}

		// Render sub components first
		foreach ($this->vars as $k => $v) {
			if ($v instanceof Component) $this->vars[$k] = strval($v);
		}
		foreach ($this->blocks as $i => $b) {
			foreach (current($b) as $k => $v) {
				if ($v instanceof Component) $this->blocks[$i][key($b)][$k] = strval($v);
			}
		}

		// Mounted event
		foreach ($this->mounted as $mounted) {
			$mounted();
		}

		// Render the current component
		foreach ($this->translators as $translator) {
			foreach ($translator->getTranslationData() as $k => $v) {
				$this->getTemplate()->setVar($k, $v);
			}
		}
		foreach ($this->vars as $k => $v) {
			$this->getTemplate()->setVar($k, $v);
		}
		foreach ($this->blocks as $b) {
			$this->getTemplate()->setBlock(key($b), current($b));
		}
		$this->render = $this->getTemplate()->getRender();
		return $this->render;
	}

	/**
	 * Concat components
	 *
	 * @param string|Component ...$elements
	 * @return Component
	 */
	public static function concat(...$elements) {
		$component = new Component();
		$component->setTemplateContent('{' . implode('}{', array_keys($elements)) . '}');
		foreach ($elements as $i => $element) {
			$component->setVar($i, $element);
		}
		return $component;
	}

	/**
	 * Dispatch an action to the appropriate method
	 *
	 * @param string $actionName
	 * @param string $defaultMethod
	 */
	protected function actionDispatch($actionName, $defaultMethod = null) {
		$method = $this->request($actionName, $defaultMethod);
		if (is_null($method)) return;
		$method = str_replace('-', '_', $method) . '_action';
		if (!method_exists($this, $method)) $method = str_replace('_', '', ucwords($method, '_'));
		if (!method_exists($this, $method)) $method = $defaultMethod . 'Action';
		if (!method_exists($this, $method)) return;
		$info = $this->getDebugTrace();
		$info['type'] = 'Action call';
		$message = 'Call method ' . $method;
		$this->log($message, $info);
		$this->$method();
	}

	/**
	 * Log a message
	 *
	 * @param string|array $message
	 * @param array info Optionnal associative array. Key available: level, type, file, line, function, class, tag
	 */
	public function log($message, array $info = array()) {
		$debugger = Debugger::getInstance();
		if (!isset($info['type'])) $info['type'] = get_class($this);
		$debugger->log($message, $info);
	}

	/**
	 * Log a warning message
	 *
	 * @param string|array $message
	 * @param array $info Optionnal associative array. Key available: type, file, line, function, class, tag
	 */
	public function logWarning($message, array $info = array()) {
		$debugger = Debugger::getInstance();
		if (!isset($info['type'])) $info['type'] = get_class($this);
		$debugger->logWarning($message, $info);
	}

	/**
	 * Log an error message
	 *
	 * @param string|array $message
	 * @param array $info Optionnal associative array. Key available: type, file, line, function, class, tag
	 */
	public function logError($message, array $info = array()) {
		$debugger = Debugger::getInstance();
		if (!isset($info['type'])) $info['type'] = get_class($this);
		$debugger->logError($message, $info);
	}

	/**
	 * Log a tagged message. A tagged message will be stored in a tag named file.
	 *
	 * @param string|array $message
	 * @param string $tag
	 * @param array $info Optionnal associative array. Key available: type, file, line, function, class, message, tag
	 */
	public function logTag($message, $tag, array $info = array()) {
		$debugger = Debugger::getInstance();
		if (!isset($info['type'])) $info['type'] = get_class($this);
		$debugger->logTag($message, $tag, $info);
	}

	/**
	 * Return debug backtrace informations
	 *
	 * @return array
	 */
	public function getDebugTrace() {
		$trace = debug_backtrace();
		$i = 1;
		if (!isset($trace[$i + 1])) $i--;
		$res['class']    = isset($trace[$i + 1]['class'])    ? $trace[$i + 1]['class']    : '';
		$res['function'] = isset($trace[$i + 1]['function']) ? $trace[$i + 1]['function'] : '';
		$res['file'] = $trace[$i]['file'];
		$res['line'] = $trace[$i]['line'];
		return $res;
	}

	/**
	 * Start time record
	 *
	 * @param string $id time record identifier
	 */
	public function timeStart($id) {
		$debugger = Debugger::getInstance();
		$debugger->timeStart($id);
	}

	/**
	 * Stop time record
	 *
	 * @param string $id time record identifier
	 */
	public function timeStop($id) {
		$debugger = Debugger::getInstance();
		$debugger->timeStop($id);
	}

	/**
	 * Return the GET parameter named $param
	 * If the parameter is not set, return the default value
	 *
	 * @param string $param GET parameter name
	 * @param mixed $default The default value
	 * @return mixed
	 */
	protected function get($param, $default = null) {
		return Http::get($param, $default);
	}

	/**
	 * Return the POST parameter named $param
	 * If the parameter is not set, return the default value
	 *
	 * @param string $param POST parameter name
	 * @param mixed $default The default value
	 * @return mixed
	 */
	protected function post($param, $default = null) {
		return Http::post($param, $default);
	}

	/**
	 * Return the COOKIE parameter named $param
	 * If the parameter is not set, return the default value
	 *
	 * @param string $param COOKIE parameter name
	 * @param mixed $default The default value
	 * @return mixed
	 */
	protected function cookie($param, $default = null) {
		return Http::cookie($param, $default);
	}

	/**
	 * Return the REQUEST parameter named $param
	 * If the parameter is not set, return the default value
	 *
	 * @param string $param REQUEST parameter name
	 * @param mixed $default The default value
	 * @return mixed
	 */
	protected function request($param, $default = null) {
		return Http::request($param, $default);
	}

	/**
	 * Return the SESSION parameter named $param
	 * If the parameter is not set, return the default value
	 *
	 * @param string $param SESSION parameter name
	 * @param mixed $default The default value
	 * @return mixed
	 */
	protected function session($param, $default = null) {
		return Http::session($param, $default);
	}

	/**
	 * Redirect to location
	 *
	 * @param string $location
	 */
	protected function redirect($location) {
		return Http::redirect($location);
	}

}