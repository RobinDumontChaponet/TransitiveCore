<?php

namespace Transitive\Core;

class Route
{
    private static function includePresenter(Route $route, bool $obClean = false)
    {
        $presenter = $route->getPresenter();
        if(is_string($presenter) && is_file($presenter)) {
	        if($obClean) {
	            ob_start();
				ob_clean();
			}
			include $presenter;
		}

        if($obClean)
            return ob_get_clean();
    }

    private static function includeView(Route $route, bool $obClean = false)
    {
        $view = $route->getView();
        if(is_string($view) && is_file($view)) {
            if($obClean) {
                ob_start();
                ob_clean();
            }
            include $view;
        }

        if($obClean)
            return ob_get_clean();
    }

    public function execute(FrontController $binder)
    {
        $obContent = '';

        if(is_string($this->presenter)) {
            if(!is_file($this->presenter)) {
                $this->view = '';
                throw new RoutingException('Not found', 404);
            }

            $this->presenter = new Presenter();
            $obContent .= self::includePresenter($this, $binder->obClean);
        } elseif(is_object($this->presenter))
            if(get_class($this->presenter) != 'Presenter')
                throw new RoutingException('Wrong type for presenter');
//         if(!$this->executed) {
            if(is_string($this->view)) {
                $this->view = new BasicView();
                $this->view->setData($this->presenter->getData());
                $obContent .= self::includeView($this, $binder->obClean);
            } elseif(is_object($this->view))
                if(get_class($this->view) != 'View')
                    throw new RoutingException('Wrong type for presenter');
                else
                    $this->view->setData($this->presenter->getData());
//         }

        return $obContent;
    }

    public function __construct($presenter, $view = null)
    {
        $this->presenter = $presenter;

        if(isset($view))
            $this->view = $view;
        elseif(is_string($this->presenter))
            $this->view = $this->presenter;
    }

    /**
     * @var Presenter | string
     */
    public $presenter;

    /**
     * @var View | string | null
     */
    public $view;

    /**
     * @return Presenter | string
     */
    public function getPresenter()
    {
        return $this->presenter;
    }

    /**
     * @return View | string | null
     */
    public function getView()
    {
        return $this->view;
    }
}
