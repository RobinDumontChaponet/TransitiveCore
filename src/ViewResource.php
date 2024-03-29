<?php

namespace Transitive\Core;

class ViewResource
{
	private string $defaultTransformer;


    public function __construct(
		public mixed $value = null,
		string $defaultTransformer = 'getValue',
    ) {
        $this->setDefault($defaultTransformer);
    }

	private static function _arrayToXML(array $data, \SimpleXMLElement &$xmlData): void
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

    private function getValue(): mixed
    {
        return $this->value;
    }

    private function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    private function setDefault(string $defaultTransformer): void
    {
        if(!method_exists($this, $defaultTransformer))
            throw new \InvalidArgumentException('Default transfomer "'.$defaultTransformer.'" is not implemented.');
        $this->defaultTransformer = $defaultTransformer;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __toString()
    {
		$result = $this->{$this->defaultTransformer}();
		if(!is_string($result))
			$result = var_export($result, true);

        return $result;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo()
    {
        return (array) $this->getValue();
    }

    /**
     * @codeCoverageIgnore
     */
    public function __get(string $name)
    {
        if(method_exists($this, $name))
            return $this->{$name}();
    }

    public function asObject(): \stdClass
    {
		/** @var \stdClass */
        return (object)(array) $this->getValue();
    }

    public function asJSON(): string
    {
        return json_encode($this->getValue());
    }

    public function asXMLElement(string $root = 'root'): \SimpleXMLElement
    {
        $xml = new \SimpleXMLElement('<'.$root.'/>');
        self::_arrayToXML($this->asArray(), $xml);

        return $xml;
    }

    public function asXML(string $root = 'root'): string
    {
        return ($xml = $this->asXMLElement($root)->asXML()) ? $xml : '';
    }

    public function asArray(): array
    {
        return (array) $this->getValue();
    }

    public function asString(string $glue = ''): string
    {
        $value = $this->asArray();
        $str = '';

        array_walk_recursive($value, function (mixed $value) use (&$str, $glue) {
            $str .= $value.$glue;
        });

        if(strlen($str) && strlen($glue))
            $str = substr($str, 0, 0 - strlen($glue));

        return $str;
    }

    public function asYAML(): string
    {
        // @codeCoverageIgnoreStart
        if(!function_exists('yaml_emit'))
            throw new \Exception('The YAML extension appears to not be installed.');
        // @codeCoverageIgnoreEnd

        return yaml_emit($this->asArray());
    }

    public function asVar(): string
    {
        return var_export($this->getValue(), true);
    }

    public function asSerialized(): string
    {
        return serialize($this->getValue());
    }
}
