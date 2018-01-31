<?php

namespace Transitive\Core;

class WebView extends BasicView implements View
{
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
        $this->metas = array();
        $this->title = '';
//         $this->content = 'No viewable content.';
        $this->content = null;
    }

    /**
     * @param string $prefix
     * @param string $separator
     * @param string $endSeparator
     *
     * @return string
     */
    public function getTitle(string $prefix = '', string $separator = ' | ', string $endSeparator = ''): string
    {
        $title = parent::getTitle();
        if(!empty($title))
            return $prefix.$separator.$title.$endSeparator;

        return $prefix;
    }

    /**
     * @param string $prefix
     * @param string $separator
     * @param string $endSeparator
     */
    public function printTitle(string $prefix = '', string $separator = ' | ', string $endSeparator = ''): void
    {
        $title = parent::getTitle();
        echo '<title>';
        if(!empty($prefix)) {
            echo $prefix;
            if(!empty($title) && !empty($separator))
                echo $separator;
        }
        echo $title;
        if(!empty($endSeparator))
            echo $endSeparator;
        echo '</title>';
    }

    /**
     * @param string $key
     */
/*
    public function printContent(string $key = null): void
    {
        if(!isset($key))
            if($this->hasContent('html'))
                $key = 'html';
        echo $this->getContent($key)->asString();
    }
*/

    /**
     * @param string $key
     */
    public function getContentValue(string $key = null): ViewResource
    {
        return new ViewResource($this->getContent($key));
    }

    public function getHeadValue(): ViewResource
    {
        return new ViewResource(array(
            'metas' => $this->getMetas(),
            'title' => $this->getTitle(),
            'scripts' => $this->getScriptsValue(),
            'styles' => $this->getStyles(),
        ), 'asArray');
    }

    public function getHead(): string
    {
        return '<head><meta charset="UTF-8">'
               .$this->getHeadValue()->asString()
               .'</head>';
    }

    public function printDocument(): void
    {
        echo $this->getDocument();
    }

    public function __debugInfo()
    {
        return array(
            'title' => $this->title,
            'metas' => $this->metas,
            'scripts' => $this->scripts,
            'styles' => $this->styles,
            'content' => $this->getContent(),
            'data' => $this->getData(),
        );
    }

    /**
     * @param string $rawTag
     */
    public function addRawMetaTag(string $rawTag): void
    {
        $this->metas[] = array(
            'raw' => $rawTag,
        );
    }

    /**
     * @param string $name
     * @param string $content
     */
    public function addMetaTag(string $name, string $content = ''): void
    {
        $this->metas[] = array(
            'name' => $name,
            'content' => $content,
        );
    }

    /**
     * @return array
     */
    public function getMetasValue(): array
    {
        return $this->metas;
    }

    public function getMetas(): string
    {
        $str = '';

        if(isset($this->metas))
            foreach($this->metas as $meta)
                if(isset($meta['name']))
                    $str .= '<meta name="'.$meta['name'].'" content="'.$meta['content'].'">';
                else
                    $str .= $meta['raw'];

        return $str;
    }

    public function printMetas(): void
    {
        echo $this->getMetas();
    }

    /**
     * @param string $content
     * @param string $type
     */
    public function addStyle(string $content, string $type = 'text/css'): void
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
    public function addScript(string $content, string $type = 'text/javascript'): void
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
    public function linkStyleSheet(string $href, string $type = 'text/css', bool $defer = false, bool $cacheBust = true, string $rel = 'stylesheet'): void
    {
        if($cacheBust)
            $href = self::cacheBust($href);
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
    public function linkScript(string $href, string $type = 'text/javascript', bool $defer = false, bool $cacheBust = true): void
    {
        if($cacheBust)
            $href = self::cacheBust($href);
        $this->scripts[] = array(
            'href' => $href,
            'type' => $type,
            'defer' => $defer,
        );
    }

    /**
     * @param string $rawTag
     */
    public function addRawStyleTag(string $rawTag): void
    {
        $this->styles[] = array(
            'raw' => $rawTag,
        );
    }

    /**
     * @param string $rawTag
     */
    public function addRawScriptTag(string $rawTag): void
    {
        $this->scripts[] = array(
            'raw' => $rawTag,
        );
    }

    /**
     * @param string $filepath
     * @param string $type
     * @param bool   $cacheBust
     *
     * @return bool
     */
    public function importStyleSheet(string $filepath, string $type = 'text/css', bool $cacheBust = false): bool
    {
        if(!is_file($filepath)) {
            trigger_error('file "'.$filepath.'" failed for import, ressource not found or not a file', E_USER_NOTICE);

            return false;
        }
        if(!is_readable($filepath)) {
            trigger_error('file "'.$filepath.'" failed for import, ressource is not readable', E_USER_NOTICE);

            return false;
        }

        if($cacheBust)
            $filepath = self::cacheBust($filepath);
        $this->addStyle(self::_getIncludeContents($filepath), $type);

        return true;
    }

    /**
     * @param string $filepath
     * @param string $type
     * @param bool   $cacheBust
     *
     * @return bool
     */
    public function importScript(string $filepath, string $type = 'text/javascript', bool $cacheBust = false): bool
    {
        if(!is_file($filepath)) {
            trigger_error('file "'.$filepath.'" failed for import, ressource not found or not a file', E_USER_NOTICE);

            return false;
        }
        if(!is_readable($filepath)) {
            trigger_error('file "'.$filepath.'" failed for import, ressource is not readable', E_USER_NOTICE);

            return false;
        }

        if($cacheBust)
            $filepath = self::cacheBust($filepath);
        $this->addScript(self::_getIncludeContents($filepath), $type);

        return true;
    }

    public function getStyles(): string
    {
        $str = '';

        if(isset($this->styles))
            foreach($this->styles as $style)
                if(isset($style['content']))
                    $str .= '<style type="'.$style['type'].'">'.$style['content'].'</style>';
                elseif(isset($style['href']))
                    $str .= '<link rel="'.$style['rel'].'" type="'.$style['type'].'" href="'.$style['href'].'"'.(($style['defer']) ? ' defer async' : '').' />';
                elseif(isset($style['raw']))
                    $str .= $style['raw'];

        return $str;
    }

    public function getStylesContent(): string
    {
        $content = '';
        if(isset($this->styles))
            foreach($this->styles as $style)
                if(isset($style['content']))
                    $content .= $style['content'].PHP_EOL;

        return $content;
    }

    public function printStyles(): void
    {
        echo $this->getStyles();
    }

    public function getScripts(): string
    {
        $str = '';

        if(isset($this->scripts))
            foreach($this->scripts as $script)
                if(isset($script['content']))
                    $str .= '<script type="'.$script['type'].'">'.$script['content'].'</script>';
                elseif(isset($script['href']))
                    $str .= '<script type="'.$script['type'].'" src="'.$script['href'].'"'.(($script['defer']) ? ' defer async' : '').'></script>';
                elseif(isset($script['raw']))
                    $str .= $script['raw'];

        return $str;
    }

    public function printScripts(): void
    {
        echo $this->getScripts();
    }

    public function getScriptsContent(): string
    {
        $content = '';
        if(isset($this->scripts))
            foreach($this->scripts as $script)
                if(isset($script['content']))
                    $content .= $script['content'];

        return $content;
    }

    /**
     * @return array
     */
    public function getScriptsValue(): array
    {
        return $this->scripts;
    }

    /**
     * @return array
     */
    public function getStylesValue(): array
    {
        return $this->styles;
    }

    public function redirect($url, $delay = 0, $code = 303) {
		$this->addRawMetaTag('<meta http-equiv="refresh" content="'.$delay.'; url='.$url.'">');

        if(!headers_sent()) {
            http_response_code($code);
            $_SERVER['REDIRECT_STATUS'] = $code;
            if($delay <= 0)
                header('Location: '.$url, true, $code);
            else
                header('Refresh:'.$delay.'; url='.$url, true, $code);

            return true;
        }

        return false;
    }
}
