<?php

namespace Transitive\Core;

interface View
{
    public function getTitle(): string;

    public function setTitle(?string $title = null): void;

    public function getContent(string $contentType = '', string $contentKey = ''): ViewResource;

    public function hasContent(string $contentType = '', string $contentKey = ''): bool;

    public function addContent(string|int|float|array|object|callable $content, string $contentType = '', string $contentKey = ''): void;

    public function getHead(): ViewResource;

    public function __toString(): string;


	public function getContentByType(string $contentType = ''): ViewResource;


	public function &getData(string $key = null): array;

	public function setData(array &$data): void;


	public function getDocument(string $contentType = '', string $contentKey = ''): ViewResource;

	public function getAllDocument(): ViewResource;
}
