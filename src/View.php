<?php

namespace Transitive\Core;

interface View
{
    public function getTitleValue(): ?string;

    public function setTitle(string $title = null): void;

    public function getContent(string $key = null): ViewRessource;

	public function getHead(): ViewRessource;

	public function __toString();
}
