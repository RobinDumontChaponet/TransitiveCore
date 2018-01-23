<?php

namespace Transitive\Core;

interface View
{
    public function getTitleValue(): ?string;

    public function setTitle(string $title = null): void;

    public function getContent(string $key = null): ViewResource;

    public function getHeadValue(): ViewResource;

    public function __toString();
}
