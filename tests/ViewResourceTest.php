<?php

declare(strict_types=1);

use Transitive\Core\ViewResource;

final class ViewResourceTest extends PHPUnit\Framework\TestCase
{
    public function testToString()
    {
        $value = 'test';

        $this->assertEquals(
            $value,
            new ViewResource($value)
        );
    }

    public function testAsObjectType()
    {
        $instance = new ViewResource('');

        $this->assertInstanceOf(
            'stdClass',
            $instance->asObject()
        );
    }

    public function testAsObjectValue()
    {
        $value = 'test';
        $instance = new ViewResource($value);

        $this->assertEquals(
            $value,
            $instance->asObject()->scalar
        );
    }

    public function testAsJSONValue()
    {
        $value = 'test';
        $instance = new ViewResource($value);

        $this->assertEquals(
            json_encode($value),
            $instance->asJSON()
        );
    }

    public function testAsXMLElementType()
    {
        $instance = new ViewResource('');

        $this->assertInstanceOf(
            'SimpleXMLElement',
            $instance->asXMLElement()
        );
    }

    public function testAsXMLType()
    {
        $instance = new ViewResource('');

        $this->assertInternalType('string',
            $instance->asXML()
        );
    }

    public function testAsArrayType()
    {
        $instance = new ViewResource('');

        $this->assertInternalType('array',
            $instance->asArray()
        );
    }

    public function testAsStringType()
    {
        $instance = new ViewResource('');

        $this->assertInternalType('string',
            $instance->asString()
        );
    }

    public function testAsStringValue()
    {
        $value = 'test';
        $instance = new ViewResource($value);

        $this->assertEquals(
            $value,
            $instance->asString()
        );
    }

    public function testAsYAMLType()
    {
        $instance = new ViewResource('');

        $this->assertInternalType('string',
            $instance->asYAML()
        );
    }

    public function testAsYAMLValue()
    {
        $value = 'test';
        $instance = new ViewResource($value);

        $this->assertEquals(
            yaml_emit(array($value)),
            $instance->asYAML()
        );
    }

    public function testAsVarType()
    {
        $instance = new ViewResource('');

        $this->assertInternalType('string',
            $instance->asVar()
        );
    }

    public function testAsVarValue()
    {
        $value = 'test';
        $instance = new ViewResource($value);

        $this->assertEquals(
            var_export($value, true),
            $instance->asVar()
        );
    }

    public function testAsSerializedType()
    {
        $instance = new ViewResource('');

        $this->assertInternalType('string',
            $instance->asSerialized()
        );
    }

    public function testAsSerializedValue()
    {
        $value = 'test';
        $instance = new ViewResource($value);

        $this->assertEquals(
            serialize($value),
            $instance->asSerialized()
        );
    }

    public function testSetDefaultNotImplemented()
    {
        $this->expectException(InvalidArgumentException::class);

        new ViewResource('', 'to be or no to be');
    }

    public function testSetValue()
    {
        $value = 'test';
        $instance = new ViewResource($value);

        $this->assertEquals(
            $value,
            $instance->value
        );
    }
}
