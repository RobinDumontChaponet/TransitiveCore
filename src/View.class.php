<?php

namespace Transitive\Core;

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

    /**
	 * cacheBust function.
	 *
	 * @param string $src
	 *
	 * @return string
	 */
	public static function cacheBust(string $src):string
	{
		$path = pathinfo($src);

		return $path['dirname'].'/'.$path['filename'].'.'.filemtime($src).'.'.$path['extension'];
	}

    public function __construct()
    {
	    $this->styles = array();
	    $this->scripts = array();
	    $this->metas = array();

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
	 * Print the view's title.
	 *
	 */
	public function printTitle():void
	{
		echo '<title>', $this->getTitle(), '</title>';
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

	/**
     * @return array
     */
    private function _getContent($content):string
    {
	    switch(gettype($content)) {
            case 'string': case 'integer': case 'double':
                return $content;
            break;
            case 'object':
                if(get_class($content) == 'Closure') {
                	ob_start();
					ob_clean();
                    $content($this->data);

					return ob_get_clean();
                } elseif(isset($content->content))
                    return $content->content;
            break;
            default:
                echo 'wrong view content type';
        }
        echo PHP_EOL;
    }

    /**
     * @param string $key
     */
	public function getContent(string $key = null):ViewRessource
	{
		$content = array();

		if($this->hasContent()) {
            if(is_array($this->content))
                if(isset($key)) {
                    if(isset($this->content[$key]))
                        $content[$key] = $this->_getContent($this->content[$key]);
                } else
                    foreach($this->content as $key => $item)
                        $content[$key] = $this->_getContent($item);
            else
                $content = $this->_getContent($this->content);

			return new ViewRessource($content);
        }
	}

    /**
     * @param string $key
     */
	public function printContent(string $key = null):void
	{
		echo $this->getContent($key)->asString();
	}

	public function getHead():ViewRessource
 	{
		return new ViewRessource(array(
            'metas' => $this->getMetas(),
            'scripts' => $this->getScripts(),
            'styles' => $this->getStyles(),
            'title' => $this->getTitle()
        ), 'asArray');
	}

	public function printHead():void
	{
		echo '<head><meta charset="UTF-8">',
			$transit->printMetas(),
			$this->printTitle(),
			'<base href="',
			(constant('SELF') == null) ? '/' : constant('SELF').'/',
			'" /><!--[if IE]><link rel="shortcut icon" href="style/favicon-32.ico"><![endif]--><link rel="icon" href="style/favicon-96.png"><meta name="msapplication-TileColor" content="#FFF"><meta name="msapplication-TileImage" content="style/favicon-144.png"><link rel="apple-touch-icon" href="style/favicon-152.png"><link rel="stylesheet" type="text/css" href="style/reset.min.css" />',
			$transit->printStyles(), '<!--[if lt IE 9]><script type="text/javascript" src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->',
			$transit->printScripts(),
			'</head>';
	}


	public function getDocument(string $contentKey = null):ViewRessource
 	{
		return new ViewRessource(array(
            'head'    => $this->getHead()->asArray,
            'content' => $this->getContent($contentKey)->asArray
        ), 'asJSON');
	}
	public function printDocument():void
 	{
		echo $this->getDocument();
	}

    /**
     * Print the view's content and header as JSON.
     */
/*
    public function outputJSON():void
    {
        $array = array(
            'metas' => $this->getMetas(),
            'scripts' => $this->getScripts(),
            'styles' => $this->getStyles(),
            'title' => $this->getTitle()
        );

		$array['content'] = $this->getContent();

        echo json_encode($array);
    }
*/

    /**
     * Print the view's content as JSON.
     */
/*
    public function displayJSONContent():void
    {
        echo json_encode($this->getContent());
    }
*/

    /**
     * @return bool
     */
    public function hasContent():bool
    {
        return isset($this->content);
    }

    public function __debugInfo()
    {
        return array(
            'metas' => $this->metas,
            'scripts' => $this->scripts,
            'styles' => $this->styles,
            'title' => $this->title,
            'data' => $this->getData()
        );
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
			'raw' => $rawTag
		);

    }

    /**
     * @param string $name
     * @param string $content
     */
    public function addMetaTag(string $name, string $content = ''):void
    {
        $this->metas[] = array(
			'name' => $name,
			'content' => $content
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
			'content' => $content
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
			'content' => $content
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
			$href = self::cacheBust($href);

		$this->styles[] = array(
			'href' => $href,
			'type' => $type,
			'defer' => $defer,
			'rel' => $rel
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
			$href = self::cacheBust($href);

        $this->scripts[] = array(
			'href' => $href,
			'type' => $type,
			'defer' => $defer
		);
    }

    /**
     * @param string $rawTag
     */
    public function addRawStyleTag(string $rawTag):void
    {
		$this->styles[] = array(
			'raw' => $rawTag
		);
    }

	/**
	 * @param string $rawTag
	 */
	public function addRawScriptTag(string $rawTag):void
    {
		$this->scripts[] = array(
			'raw' => $rawTag
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
	    if(!file_exists($filepath)) {
			throw new \Exception(__METHOD__.'file "'.$filepath.'" failed for import, doesn\'t exists');
			return false;
		}

        if($cacheBust)
			$filepath = self::cacheBust($filepath);

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
		if(!file_exists($filepath)) {
			throw new \Exception(__METHOD__.'file "'.$filepath.'" failed for import, doesn\'t exists');
			return false;
		}

		if($cacheBust)
			$filepath = self::cacheBust($filepath);

		$this->addScript(get_include_contents($filepath), $type);

		return true;
	}

    public function printStyles():void
    {
        if(isset($this->styles))
            foreach($this->styles as $style)
            	if(isset($style['content']))
	                echo '<style type="'.$style['type'].'">'.$style['content'].'</style>';
				elseif(isset($style['href']))
					echo '<link rel="'.$style['rel'].'" type="'.$style['type'].'" href="'.$style['href'].'"'.(($style['defer']) ? ' defer async' : '').' />';
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

	/**
	 * @return array
	 */
	public function &getData():array
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

	public function getBody(string $key = null):ViewRessource
    {
		return $this->getContent($key);
    }

    public function printBody(string $key = null):void
    {
		$this->printContent($key);
    }
}
