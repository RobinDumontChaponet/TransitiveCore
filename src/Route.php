<?php

namespace Transitive\Core;

/**
 * Route class.
 */
class Route
{
    private static function _include($exposedVariables, $_prefix) {
        extract($exposedVariables, (!empty($_prefix)) ? EXTR_PREFIX_ALL : null, $_prefix);
        unset($exposedVariables);

        include ${$_prefix.((!empty($_prefix)) ? '_' : '').'path'};
    }

    private static function includePresenter(string $path, array $exposedVariables = [], string $_prefix = null, bool $obClean = true)
    {
        if($obClean) {
            ob_start();
            ob_clean();
        }

        self::_include(['path' => $path, 'obClean' => $obClean] + $exposedVariables, $_prefix);

        if($obClean)
            return ob_get_clean();
    }

    private static function includeView(string $path, array $exposedVariables = [], string $_prefix = null, bool $obClean = true)
    {
        if($obClean) {
            ob_start();
            ob_clean();
        }

        self::_include(['path' => $path, 'obClean' => $obClean] + $exposedVariables, $_prefix);

        if($obClean)
            return ob_get_clean();
    }

    public function execute(array $exposedVariablesPresenter = null, array $exposedVariablesView = null, bool $obClean = true)
    {
        $obContent = '';
        if(!is_array($exposedVariablesView) && is_array($exposedVariablesPresenter))
            $exposedVariablesView = $exposedVariablesPresenter;

        // Presenter
        $presenter = $this->getPresenter();

        if(is_string($presenter)) {
            if(is_file($presenter)) {
                $presenter = new Presenter();

                $obContent .= self::includePresenter($this->getPresenter(), $exposedVariablesPresenter + ['presenter' => $presenter], $this->prefix, $obClean);

                $this->setPresenter($presenter);
            } else {
                $this->setView();
                throw new RoutingException('Presenter not found', 404);
            }
        }

        // View
        $view = $this->getView();

        if(is_string($view)) {
            if(is_file($view)) {
                $view = new WebView();

                $obContent .= self::includeView($this->getView(), $exposedVariablesView + ['view' => $view], $this->prefix, $obClean);

                $this->setView($view);
            } else {
                throw new RoutingException('View not found', 404);
            }
        }

        if(is_object($this->view))
            $this->view->setData($this->presenter->getData());

        return $obContent;
    }

    public function __construct($presenter, $view = null, string $prefix = null)
    {
        $this->presenter = $presenter;
        $this->view = $view;
        $this->prefix = $prefix;
    }

    /**
     * @var string | null : prefix for exposed variables
     */
    private $prefix;

    /**
     * @var Presenter | string
     */
    public $presenter;

    /**
     * @var View | string | null
     */
    public $view;

    public function hasPresenter(): bool
    {
        return isset($this->presenter) && $this->presenter instanceof Presenter;
    }

    /**
     * @return Presenter | string
     */
    public function getPresenter()
    {
        return $this->presenter;
    }

    public function setPresenter(Presenter $presenter)
    {
        return $this->presenter = $presenter;
    }

    public function hasView(): bool
    {
        return isset($this->view) && $this->view instanceof View;
    }

    /**
     * @return View | string | null
     */
    public function getView()
    {
        return $this->view;
    }

    public function setView(View $view = null)
    {
        return $this->view = $view;
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
        $title = $this->view->getTitle();
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
        $title = (isset($this->view)) ? $this->view->getTitle() : '';
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
    public function hasContent(string $key = null): bool
    {
        if(isset($this->view))
            return $this->view->hasContent($key);
    }

    /**
     * @param string $key
     */
    public function getContent(string $key = null)
    {
        if(isset($this->view))
            return $this->view->getContent($key);
    }

    /**
     * @param string $key
     */
/*
    public function printContent(string $key = null): void
    {
        if(isset($this->view))
            $this->view->printContent($key);
    }
*/
    public function getHead(): ViewResource
    {
        if(isset($this->view))
            return $this->view->getHeadValue();
    }

    public function printHead(): void
    {
        if(isset($this->view))
            $this->view->printHead();
    }

    public function getBody()
    {
        if(isset($this->view))
            return $this->view->getBody();
    }

    public function printBody(): void
    {
        if(isset($this->view))
            $this->view->printBody();
    }

    public function getDocument()
    {
        if(isset($this->view))
            return $this->view->getDocument();
    }

    public function printDocument(): void
    {
        if(isset($this->view))
            $this->view->printDocument();
    }
}
