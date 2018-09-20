<?php

namespace Transitive\Simple;

use Transitive\Core;

class View implements Core\View
{
    /**
     * The view's title.
     *
     * @var string
     */
    public $title;

    /**
     * content.
     *
     * @var mixed
     */
    public $content;

    /**
     * data pushed from the presenter.
     *
     * @var array
     */
    public $data = [];

    /**
     * cacheBust function.
     *
     * @param string $src
     *
     * @return string
     */
    public static function cacheBust(string $src): string
    {
        if(!file_exists($src))
            return $src;

        $path = pathinfo($src);

        return $path['dirname'].'/'.$path['filename'].'.'.filemtime($src).'.'.$path['extension'];
    }

    /*
     * @param string $include
     * @return string
     */
    protected static function _getIncludeContents(string $include): string
    {
        ob_start();
        include $include;

        return ob_get_clean();
    }

    public function __construct()
    {
        $this->title = '';
        $this->content = 'No viewable content.';
    }

    /**
     * Get the view's title.
     *
     * @return string
     */
    public function getTitleValue(): ?string
    {
        return $this->title;
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
    public function setTitle(string $title = null): void
    {
        $this->title = $title;
    }

    /**
     * @param mixed content
     *
     * @return mixed
     */
    protected function _getContent($content)
    {
        switch(gettype($content)) {
            case 'string': case 'integer': case 'double':
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
     * @param string $key
     *
     * @return Core\ViewResource
     */
    public function getContent(string $key = null): Core\ViewResource
    {
        $content = null;
        if($this->hasContent($key)) {
            if(is_array($this->content))
                if(isset($key))
                    $content = $this->_getContent($this->content[$key]);
                else {
                    $content = array();
                    foreach($this->content as $key => $item)
                        $content[$key] = $this->_getContent($item);
                }
            else
                $content = $this->_getContent($this->content);
        }

        return new Core\ViewResource($content);
    }

    /*
     * @return Core\ViewResource
     */
    public function getHead(): Core\ViewResource
    {
        return new Core\ViewResource(array(
            'title' => $this->getTitleValue(),
        ), 'asArray');
    }

    /*
     * @return string
     */
    public function getHeadValue(): string
    {
        return $this->getHead()->__toString();
    }

    /*
     * @param string $content = null
     * @return Core\ViewResource
     */
    public function getDocument(string $contentKey = null): Core\ViewResource
    {
        return new Core\ViewResource(array(
            'head' => $this->getHeadValue()->asArray,
            'content' => $this->getContent($contentKey)->asArray,
        ), 'asJSON');
    }

    /*
     * @return string
    */
    public function getDocumentValue(): string
    {
        return $this->getDocument()->__toString();
    }

    /**
     * @param string $key = null
     *
     * @return bool
     */
    public function hasContent(string $key = null): bool
    {
        if(isset($key))
            return is_array($this->content) && isset($this->content[$key]);
        else
            return isset($this->content);
    }

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo()
    {
        return array(
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'data' => $this->getData(),
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function __toString(): string
    {
        return $this->getContent()->asString();
    }

    /**
     * @param string $key = null
     *
     * @return array
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