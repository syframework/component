<?php
namespace Sy;

use Sy\Template\TemplateProvider,
	Sy\Template\TemplateFileNotFoundException,
	Sy\Debug\Debugger;

class Component {

	/**
	 * Template engine
	 *
	 * @var ITemplate
	 */
	private $template;

	/**
	 * Template type
	 *
	 * @var string
	 */
	private $templateType;

	public function __construct() {
		$this->templateType = '';
		$this->template = TemplateProvider::createTemplate();
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
			$this->template->setFile($file);
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
		$this->template->setContent($content);
	}

	/**
	 * Set a value of a variable
	 *
	 * @param string $var
	 * @param mixed $value
	 * @param bool $append
	 */
	public function setVar($var, $value, $append = false) {
		$this->template->setVar($var, strval($value), $append);
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
	 * Parse a block
	 *
	 * @param string $block
	 */
	public function setBlock($block) {
		$this->template->setBlock($block);
	}

	/**
	 * Add a component
	 *
	 * @param string $where
	 * @param Component $component
	 * @param boolean $append
	 */
	public function setComponent($where, Component $component, $append = false) {
		$this->template->setVar($where, $component->__toString(), $append);
	}

	/**
	 * Return the render of the component
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->template->getRender();
	}

	/**
	 * Render the component
	 */
	public function render() {
		echo $this->__toString();
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
		$method .= 'Action';
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