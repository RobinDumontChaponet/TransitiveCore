<?php

namespace Transitive\Core;

interface View
{
    public function getTitle(): string;

    public function setTitle($title = null): void;

    public function getContent(string $key = null): ViewResource;

    public function hasContent(?string $contentType = null, ?string $contentKey = null): bool;

    public function addContent($content, ?string $contentType = null, ?string $contentKey = null): void;

    public function getHead(): ViewResource;

    public function __toString();
}
