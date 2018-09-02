<?php

namespace Transitive\Core;

interface View
{
    public function getTitle(): string;

    public function setTitle(string $title = null): void;

    public function getContent(string $key = null): ViewResource;

    public function getHead(): ViewResource;

    public function __toString();
}
