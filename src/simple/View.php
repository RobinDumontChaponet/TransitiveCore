<?php

namespace Transitive\Simple;

use Transitive\Core;

class View implements Core\View
{
    /**
     * data pushed from the presenter.
     */
    public array $data = [];

    public function __construct(
		/**
		 * The view's title.
		 */
		public mixed $title = '',
		/**
		 * Array of content (string or scalar).
		 */
		private array $content = [],
	)
    {}

	/**
	 * Add timestamp to path
	 */
	public static function cacheBust(string $src): string
	{
		if(!file_exists($src))
			return $src;

		$path = pathinfo($src);

		return $path['dirname'].'/'.$path['filename'].'.'.filemtime($src).'.'.$path['extension'];
	}

	/*
	 * @param string $include path
	 * @return string buffer output
	 */
	protected static function _getIncludeContents(string $include): string
	{
		ob_start();
		include $include;

		return ob_get_clean();
	}

    /**
     * Get the view's title value.
     */
    public function getTitleValue(): ?string
    {
        $title = $this->title;

        switch(gettype($title)) {
            case 'string': case 'string': case 'integer': case 'double': case 'float':
                return $title;
            break;
            case 'object':
                if('Closure' == get_class($title)) {
                    ob_start();
                    ob_clean();
                    $returned = $title($this->data);
                    $output = ob_get_clean();
                    if(isset($returned))
                        return $returned;
                    else
                        return $output;
                }
            break;
            default:
                throw new \InvalidArgumentException('wrong title content type : '.gettype($title));
        }

        return null;
    }

    /**
     * Get the view's title.
     */
    public function getTitle(string $prefix = '', string $separator = ' | ', string $sufix = ''): string
    {
        if(empty($this->getTitleValue()))
            $separator = '';

        return $prefix.$separator.$this->getTitleValue().$sufix;
    }

    /**
     * Set the view's title.
     *
     * @param string $title
     */
    public function setTitle(mixed $title = null): void
    {
        if(in_array(gettype($title), ['string', 'integer', 'double', 'float']) || empty($title) || 'object' == gettype($title) && 'Closure' == get_class($title))
            $this->title = $title;
        else
            throw new \InvalidArgumentException('wrong view content type : '.gettype($title));
    }

    /**
	 * return true if content with key exists
     */
    public function hasContent(?string $contentType = null, ?string $contentKey = null): bool
    {
        return isset($this->content[$contentType][$contentKey]);
    }

    /**
     *
     */
    protected function _getContent(mixed $content): mixed
    {
        if(!isset($content))
            return null;

        if(is_array($content))
            return array_map(
                function ($value) {
                    return $this->_getContent($value);
                }, $content
            );

        switch(gettype($content)) {
            case 'string': case 'integer': case 'double': case 'float':
                return $content;
            break;
            case 'object':
                if('Closure' == get_class($content)) {
                    ob_start();
                    ob_clean();
                    $returned = $content($this->data);
                    $output = ob_get_clean();
                    if(isset($returned))
                        return $returned;
                    else
                        return $output;
                } elseif(isset($content->content))
                    return $content->content;
            break;
            default:
                throw new \InvalidArgumentException('wrong view content type : '.gettype($content));
        }
    }

    /**

     */
    public function getContent(?string $contentType = null, ?string $contentKey = null): Core\ViewResource
    {
        return new Core\ViewResource($this->_getContent(@$this->content[$contentType][$contentKey]));
    }

    public function getAllContent(): Core\ViewResource
    {
        return new Core\ViewResource(array_map(
            function ($type, $value) {
                return [
                    $type => array_map(
                        function ($key, $value) {
                            return [
                                $key => $this->_getContent($value),
                            ];
                        }, array_keys($value), $value
                    ),
                ];
            }, array_keys($this->content), $this->content
        ));
    }

    public function getContentByType(string $contentType = null): Core\ViewResource
    {
        if(empty($contentType))
            return new Core\ViewResource();

        $content = array_merge(...array_map(
            function ($key, $value) {
                return [
                    $key => $this->_getContent($value),
                ];
            }, array_keys($this->content[$contentType]), $this->content[$contentType]
        ));

        if(1 == count($content) && empty(key($content)))
            $content = $content[key($content)];

        return new Core\ViewResource($content);
    }

    public function addContent($content, ?string $contentType = null, ?string $contentKey = null): void
    {
        if(is_array($content)) {
            if(isset($content['content'])) {
                $content = $content['content'];
                if(isset($content['key']))
                    $contentKey = $content['key'];
                if(isset($content['type']))
                    $contentType = $content['type'];
            }
        }

/*
        if(isset($this->content[$contentType][$contentKey]))
            trigger_error('Replacing existing content with key "'.((is_string($contentKey))?$contentKey:'{unnamed}').'" and type "'.((is_string($contentType))?$contentType:'{all}').'"');
*/

        $this->content[$contentType][$contentKey] = $content;
    }

    /*
     */
    public function getHead(): Core\ViewResource
    {
        return new Core\ViewResource([
            'title' => $this->getTitleValue(),
        ], 'asArray');
    }

    /*
     */
    public function getHeadValue(): string
    {
        return $this->getHead()->__toString();
    }

    /*
     */
    public function getDocument(?string $contentType = null, ?string $contentKey = null): Core\ViewResource
    {
        return new Core\ViewResource([
            'head' => $this->getHead()->asArray,
            'content' => $this->getContent($contentKey, $contentType)->asArray,
        ], 'asJSON');
    }

    /*
     */
    public function getAllDocument(): Core\ViewResource
    {
        return new Core\ViewResource([
            'head' => $this->getHead()->asArray,
            'content' => $this->getAllContent()->asArray,
        ], 'asJSON');
    }

    /*
    */
    public function getDocumentValue(): string
    {
        return $this->getDocument()->__toString();
    }

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo()
    {
        return [
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'data' => $this->getData(),
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function __toString(): string
    {
        if($this->hasContent())
            return $this->getContent()->asString();
        else
            trigger_error('View has no content');

        return '';
    }

    /**
     */
    public function &getData(string $key = null): array
    {
        if(isset($key))
            return $this->data[$key];
        else
            return $this->data;
    }

    /**
     * @param array &$data
     */
    public function setData(array &$data): void
    {
        $this->data = $data;
    }
}
