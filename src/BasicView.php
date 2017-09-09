<?php

namespace Transitive\Core;

class BasicView implements View
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
    public $data;

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

    private static function _getIncludeContents($include): string
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
    final public function getTitleValue(): ?string
    {
        return $this->title;
    }

    /**
     * Get the view's title.
     */
    public function getTitle(): string
    {
        return $this->getTitleValue();
    }

    /**
     * Set the view's title.
     *
     * @param string $title
     */
    public function setTitle(string $title = ''): void
    {
        $this->title = $title;
    }

    /**
     * @return
     */
    protected function _getContent($content)
    {
        switch(gettype($content)) {
            case 'string': case 'integer': case 'double':
                return $content;
            break;
            case 'object':
                if(get_class($content) == 'Closure') {
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
                throw new Exception('wrong view content type');
        }
    }

    /**
     * @param string $key
     */
    public function getContent(string $key = null): ViewRessource
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

        return new ViewRessource($content);
    }

    public function getHeadValue(): ViewRessource
    {
        return new ViewRessource(array(
            'title' => $this->getTitle(),
        ), 'asArray');
    }

    public function getHead(): string
    {
        return $this->getHeadValue()->asString();
    }

    public function getDocumentValue(string $contentKey = null): ViewRessource
    {
        return new ViewRessource(array(
            'head' => $this->getHead()->asArray,
            'content' => $this->getContent($contentKey)->asArray,
        ), 'asJSON');
    }

    public function getDocument(): string
    {
        return $this->getDocument()->__toString();
    }

    /**
     * @return bool
     */
    public function hasContent(string $key = null): bool
    {
        if(isset($key))
            return is_array($this->content) && isset($this->content[$key]);
        else
            return isset($this->content);
    }

    public function __debugInfo()
    {
        return array(
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'data' => $this->getData(),
        );
    }

    public function __toString(): string
    {
        return $this->getContent();
    }

    /**
     * @return array
     */
    public function &getData(string $key = null)
    {
        if(isset($key))
            return $this->data[$key];
        else
            return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array &$data): void
    {
        $this->data = $data;
    }
}
