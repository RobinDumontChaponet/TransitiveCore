<?php

namespace Transitive\Core;

class ViewResource
{
    /**
     * @var mixed
     */
    public $value;
    private $default;

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

    public function __construct($value = null, $defaultTransformer = 'getValue')
    {
        $this->setValue($value);
        $this->setDefault($defaultTransformer);
    }

    private function getValue()
    {
        return $this->value;
    }

    private function setValue($value): void
    {
        $this->value = $value;
    }

    private function setDefault($default): void
    {
        if(!method_exists($this, $default))
            throw new Exception('Default transfomer "'.$default.'" is not implemented.');
        $this->default = $default;
    }

    public function __toString()
    {
        return $this->{$this->default}() ?? '';
    }

    public function __debugInfo()
    {
        return (array) $this->getValue();
    }

    public function __get($name)
    {
        if(method_exists($this, $name))
            return $this->{$name}();
    }

    public function asObject(): \stdClass
    {
        return (object) $this->getValue();
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

        array_walk_recursive($value, function ($value, $key) use (&$str, $glue) {
            $str .= $value.$glue;
        });

        $str = substr($str, 0, 0 - strlen($glue));

        return $str;
    }

    public function asYAML(): string
    {
        if(!function_exists('yaml_emit'))
            throw new \Exception('The YAML extension appears to not be installed.');
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
