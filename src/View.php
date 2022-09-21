<?php

namespace Transitive\Core;

interface View
{
	/*
	 * should return the view's title as a string. It may be ''.
	 */
    public function getTitle(): string;

    public function setTitle(?string $title = null): void;

	/*
	 * should return entire content or corresponding content as ViewResource.
	 */
    public function getContent(string $contentType = '', string $contentKey = ''): ViewResource;

	/*
	 * should return true if the view has content corresponding to parameters or any content at all.
	 */
    public function hasContent(string $contentType = '', string $contentKey = ''): bool;

    public function addContent(string|int|float|array|object|callable $content, string $contentType = '', string $contentKey = ''): void;

	/*
	 * should return the head of the view, that is the metadata, such as the title and any additional metadata as fit for the implementation.
	 */
    public function getHead(): ViewResource;

	/*
	 * may call getDocument or getContent. Preferably getDocument.
	 */
    public function __toString(): string;

	/*
	 * @deprecated
	 */
	public function getContentByType(string $contentType = ''): ViewResource;

	/*
	 * these are the working data, as presented by the Presenter.
	 */
	public function &getData(string $key = null): array;

	/*
	 * these are the working data, as presented by the Presenter.
	 */
	public function setData(array &$data): void;


	/*
	 * should return the head of the view and its content (every content or corresponding to parameters). May call getHead and getContent.
	 */
	public function getDocument(string $contentType = '', string $contentKey = ''): ViewResource;

	/*
	 * ?
	 * @deprecated
	 */
	public function getAllDocument(): ViewResource;
}
