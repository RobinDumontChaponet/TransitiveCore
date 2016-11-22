<?php

namespace Transitive\Core;

/**
 * cacheBust function.
 *
 * @param string $src
 *
 * @return string
 */
function cacheBust(string $src):string
{
	$path = pathinfo($src);

	return $path['dirname'].'/'.$path['filename'].'.'.filemtime($src).'.'.$path['extension'];
}

class View {
	/**
	 * The view's title.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * styles.
	 *
	 * @var array
	 */
	public $styles;

    /**
     * scripts.
     *
     * @var array
     */
    public $scripts;

    /**
     * metas.
     *
     * @var array
     */
    public $metas;

    /**
     * content.
     *
     * @var mixed
     */
    public $content;

    /**
     * data.
     *
     * @var array
     */
    public $data;

    public function __construct()
    {
	    $this->styles = array();
	    $this->scripts = array();

	    $this->content = 'No viewable content.';
    }

	/**
	 * Get the view's title.
	 *
	 * @return string
	 */
	public function getTitle():string
	{
        return $this->title;
    }

    /**
     * Set the view's title.
     *
     * @param string $title
     */
    public function setTitle(string $title = ''):void
    {
        $this->title = $title;
    }

/*
    private function _getContent():string
    {
        // TODO: implement here
    }
*/

    /**
     * @param mixed $content
     */
    private function _displayHTML($content):void
    { // used by displayContent()
        switch(gettype($content)) {
            case 'string': case 'integer': case 'double':
                echo $content;
            break;
            case 'object':
                if(get_class($content) == 'Closure')
                    $content($this->data);
                elseif(isset($content->content))
                    echo $content->content;
            break;
            default:
                echo 'wrong view content type';
        }
        echo PHP_EOL;
    }

    /**
     * @param string $key
     */
    public function displayHTMLContent(string $key = null):void
    {
        if($this->hasContent()) {
            if(gettype($this->content) == 'array')
                if(isset($key)) {
                    if(isset($this->content[$key]))
                        $this->_displayHTML($this->content[$key]);
                } else
                    foreach($this->content as $item)
                        $this->_displayHTML($item);
            else
                $this->_displayHTML($this->content);
        }
    }

    /**
     * @return array
     */
    private function _getContent():array
    { // used for json outputs and __toString
        $contentParts = array();
        if($this->hasContent()) {
            if(gettype($this->content) == 'array')
                foreach($this->content as $key => $item) {
                    ob_start();
                    ob_clean();
                    $this->_displayHTML($item);
                    $contentParts[$key] = ob_get_clean();
                }
            else {
                ob_start();
                ob_clean();
                $this->displayHTMLContent();
                $contentParts['content'] = ob_get_clean();
            }
        }

        return $contentParts;
    }

    /**
     * Print the view's content and header as JSON.
     */
    public function outputJSON():void
    {
        $array = array(
            'metaTags' => $this->getMetaTags(),
            'scripts' => $this->getScripts(),
            'scriptLinks' => $this->scriptLinks,
            'linkTags' => $this->getLinkTags(),
            'styles' => $this->getStyle(),
            'title' => $this->getTitle(),
        );

        $content = $this->_getContent();
        if(count($content) > 1)
            $array['content'] = $content;
        else
            $array['content'] = $content['content'];

        echo json_encode($array);
    }

    /**
     * Print the view's content as JSON.
     */
    public function displayJSONContent():void
    {
        echo json_encode($this->_getContent());
    }

    /**
     * @return bool
     */
    public function hasContent():bool
    {
        return isset($this->content);
    }

/*
    public function __debugInfo()
    {
        return array(
            'metaTags' => $this->metaTags,
            'scriptTags' => $this->scriptTags,
            'scriptLinks' => $this->scriptLinks,
            'script' => $this->script,
            'linkTags' => $this->linkTags,
            'style' => $this->style,
            'title' => $this->title,
            'data' => $this->data,
        );
    }
*/

    public function print():void
    {
        // TODO: implement here
    }

    public function __toString():string
    {
        // TODO: implement here
    }

    /**
     * @param string $rawTag
     */
    public function addRawMetaTag(string $rawTag):void
    {
        $this->metas[] = array(
			'raw' => $rawTag,
		);

    }

    /**
     * @param string $name
     * @param string $content
     */
    public function addMetaTag(string $name, string $content = ''):void
    {
        $this->scripts[] = array(
			'name' => $name,
			'content' => $content,
		);
    }

	/**
	 * @return array
	 */
	public function getMetas():array
	{
        return $this->metas;
    }

    public function printMetas():void
    {
		if(isset($this->metas))
            foreach($this->metas as $meta)
				echo '<meta name="'.$meta['name'].'" content="'.$meta['content'].'">';
	}

	/**
	 * @param string $content
	 * @param string $type
	 */
	public function addStyle(string $content, string $type = 'text/css'):void
	{
		$this->styles[] = array(
			'type' => $type,
			'content' => $content,
		);
	}

	/**
	 * @param string $content
	 * @param string $type
	 */
	public function addScript(string $content, string $type = 'text/javascript'):void
	{
		$this->scripts[] = array(
			'type' => $type,
			'content' => $content,
		);
	}

    /**
     * @param string $href
     * @param string $type
     * @param bool   $defer
     * @param bool   $cacheBust
     * @param string $rel
     */
    public function linkStyleSheet(string $href, string $type = 'text/css', bool $defer = false, bool $cacheBust = true, string $rel = 'stylesheet'):void
    {
	    if($cacheBust)
			$href = cacheBust($href);

		$this->styles[] = array(
			'href' => $href,
			'type' => $type,
			'defer' => $defer,
			'rel' => $rel,
		);
    }

    /**
     * @param string $href
     * @param string $type
     * @param bool   $defer
     * @param bool   $cahceBust
     */
    public function linkScript(string $href, string $type = 'text/javascript', bool $defer = false, bool $cacheBust = true):void
    {
	    if($cacheBust)
			$href = cacheBust($href);

        $this->scripts[] = array(
			'href' => $href,
			'type' => $type,
			'defer' => $defer,
		);
    }

    /**
     * @param string $rawTag
     */
    public function addRawStyleTag(string $rawTag):void
    {
		$this->styles[] = array(
			'raw' => $rawTag,
		);
    }

	/**
	 * @param string $rawTag
	 */
	public function addRawScriptTag(string $rawTag):void
    {
		$this->scripts[] = array(
			'raw' => $rawTag,
		);
    }

/*
	public function import(string $href, string $type='text/css', bool $cacheBust=true, bool $defer=false, string $rel='stylesheet'):bool
	{

	}
*/

    /**
     * @param string $filepath
     * @param string $type
     * @param bool   $cacheBust
     *
     * @return bool
     */
    public function importStyleSheet(string $filepath, string $type = 'text/css', bool $cacheBust = true):bool
    {
        if($cacheBust)
			$filepath = cacheBust($filepath);

		$this->addStyle(get_include_contents($filepath), $type);
    }

	/**
	 * @param string $filepath
	 * @param string $type
	 * @param bool $cacheBust
 *
	 * @return bool
	 */
	public function importScript(string $filepath, string $type = 'text/javascript', bool $cacheBust = true):bool
	{
		if($cacheBust)
			$filepath = cacheBust($filepath);

		$this->addScript(get_include_contents($filepath), $type);
	}

    private function _getHeader():string
    {
        // TODO: implement here
    }

    public function printStyles():void
    {
        if(isset($this->styles))
            foreach($this->styles as $style)
            	if(isset($style['content']))
	                echo '<style type="'.$style['type'].'">'.$style['content'].'</script>';
				elseif(isset($style['href']))
					echo '<link rel="'.$style['rel'].'" type="'.$style['type'].'" href="'.$style['href'].'"'.(($script['defer']) ? ' defer async' : '').' />';
				elseif(isset($style['raw']))
	                echo $style['raw'];
    }

    public function printScripts():void
    {
		if(isset($this->scripts))
            foreach($this->scripts as $script)
            	if(isset($script['content']))
	                echo '<script type="'.$script['type'].'">'.$script['content'].'</script>';
				elseif(isset($script['href']))
	                echo '<script type="'.$script['type'].'" src="'.$script['href'].'"'.(($script['defer']) ? ' defer async' : '').'></script>';
				elseif(isset($script['raw']))
	                echo $script['raw'];
    }

    /**
     * @return array
     */
    public function getScripts():array
    {
        return $this->scripts;
    }

    /**
     * @return array
     */
    public function getStyles():array
    {
        return $this->styles;
    }

    public function removeStyleTag():bool
    {
        // TODO: implement here
    }

    public function removeScriptTag():bool
    {
        // TODO: implement here
    }

    public function removeImported():bool
    {
        // TODO: implement here
    }

	/**
	 * @return array
	 */
	public function getData():array
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData(array &$data):void
	{
		$this->data = $data;
	}
}
