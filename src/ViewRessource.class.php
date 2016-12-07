<?php

namespace Transitive\Core;

class ViewRessource {
	/**
	 * @var mixed
	 */
	public $value;
	private $default;

	private static function _arrayToXML(array $data, \SimpleXMLElement &$xmlData):void
	{
		foreach($data as $key => $value) {
	        if(is_numeric($key))
	            $key = 'item'.$key;
	        if(is_array($value)) {
	            $subnode = $xmlData->addChild($key);
	            self::_arrayToXML($value, $subnode);
	        } else
	            $xmlData->addChild($key, htmlspecialchars($value));
		}
	}

	public function __construct($value = null, $defaultTransformer = 'getValue')
	{
		$this->setValue($value);
		$this->setDefault($defaultTransformer);
	}

	private function &getValue()
	{
		return $this->value;
	}

	private function setValue(&$value):void
	{
		$this->value = $value;
	}

	private function setDefault($default):void
	{
		if(!method_exists($this,  $default))
			throw new Exception('Default transfomer "'.$default.'" is not implemented.');

		$this->default = $default;
	}

	public function __toString()
	{
		return $this->{$this->default}();
	}

	public function __debugInfo()
	{
		return (array)$this->getValue();
	}

	public function __get($name)
	{
		if(method_exists($this, $name))
			return $this->{$name}();
	}

	public function asObject():\stdClass
	{
		return (object)$this->getValue();
	}
	public function asJSON():string
	{
		return json_encode($this->getValue());
	}
	public function asXMLElement():\SimpleXMLElement
	{
		$xml = new \SimpleXMLElement('<root/>');
		self::_arrayToXML($this->getValue(), $xml);

		return $xml;
	}
	public function asXML():string
	{
		return ($xml = $this->asXMLElement()->asXML())?$xml:'';
	}
	public function asArray():array
	{
		if(is_array($this->getValue()))
			return $this->getValue();
		else
			return array($this->getValue());
	}
/*
	public function asString($param):string
	{
		return '';
	}
*/
	public function asYAML():string
	{
		if(!function_exists('yaml_emit'))
			throw new \Exception('The YAML extension appears to not be installed.');

		return yaml_emit($this->asArray());
	}

	public function asVar():string
	{
		return var_export($this->getValue(), true);
	}

	public function asSerialized():string
	{
		return serialize($this->getValue());
	}
}