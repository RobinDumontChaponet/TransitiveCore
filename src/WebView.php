<?php

namespace Transitive\Core;

class WebView extends BasicView implements View
{
    /**
     * styles tags and linked scripts.
     *
     * @var array
     */
    public $styles;

    /**
     * scripts tags and linked scripts.
     *
     * @var array
     */
    public $scripts;

    /**
     * metas tags.
     *
     * @var array
     */
    public $metas;

    public function __construct()
    {
        $this->styles = array();
        $this->scripts = array();
        $this->metas = array();
        $this->title = '';
        $this->content = null;
    }

    /**
     * @param string $prefix
     * @param string $separator
     * @param string $endSeparator
     */
    public function getTitle(string $prefix = '', string $separator = ' | ', string $sufix = ''): string
    {
        return parent::getTitle('<title>'.$prefix, $separator, $sufix.'</title>');
    }

    /*
     * @return ViewResource
     */
    public function getHeadValue(): ViewResource
    {
        return new ViewResource(array(
            'metas' => $this->getMetasValue(),
            'title' => $this->getTitleValue(),
            'scripts' => $this->getScriptsValue(),
            'styles' => $this->getStylesValue(),
        ), 'asArray');
    }

    /*
     * @return string
     */
    public function getHead(): string
    {
        return '<head><meta charset="UTF-8">'
               .$this->getHeadValue()->asString()
               .'</head>';
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

    /*
     * @return string
     */
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
     * @param string $type      = 'text/javascript'
     * @param bool   $defer     = false
     * @param bool   $cacheBust = true
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
     * @param string $type      = 'text/css'
     * @param bool   $cacheBust = false
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
     * @param string $type      = 'text/javascript'
     * @param bool   $cacheBust = false
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

    /*
     * @return string
     */
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

    /*
     * @return string
     */
    public function getStylesContent(): string
    {
        $content = '';
        if(isset($this->styles))
            foreach($this->styles as $style)
                if(isset($style['content']))
                    $content .= $style['content'].PHP_EOL;

        return $content;
    }

    /*
     * @return string
     */
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

    /*
     * @return string
     */
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

    /*
     * @param string url
     * @param int delay = 0
     * @param int code = 303
     * @return bool
     */
    public function redirect(string $url, int $delay = 0, int $code = 303): bool
    {
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
