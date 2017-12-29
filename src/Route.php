<?php

namespace Transitive\Core;

class Route
{
	private static function _include($exposedVariables, $_prefix) {
		extract($exposedVariables, (!empty($_prefix))?EXTR_PREFIX_ALL:null, $_prefix);
		unset($exposedVariables);

		include ${$_prefix.((!empty($_prefix))?'_':'').'path'};
	}

    private static function includePresenter(string $path, array $exposedVariables = [], string $_prefix = null, bool $obClean = true)
    {
		if($obClean) {
            ob_start();
			ob_clean();
		}

		self::_include(['path'=>$path, 'obClean'=>$obClean]+$exposedVariables, $_prefix);

        if($obClean)
            return ob_get_clean();
    }

    private static function includeView(string $path, array $exposedVariables = [], string $_prefix = null, bool $obClean = true)
    {
        if($obClean) {
            ob_start();
			ob_clean();
		}

		self::_include(['path'=>$path, 'obClean'=>$obClean]+$exposedVariables, $_prefix);

        if($obClean)
            return ob_get_clean();
    }

    public function execute(FrontController $binder)
    {
        $obContent = '';

		// Presenter
		$presenter = $this->getPresenter();

        if(is_string($presenter)) {
        	if(is_file($presenter)) {
		        $presenter = new Presenter();

		        $obContent .= self::includePresenter($this->getPresenter(), ['binder'=>$binder, 'presenter'=>$presenter], $this->prefix, $binder->obClean);

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

		 	   $obContent .= self::includeView($this->getView(), ['view'=>$view], $this->prefix, $binder->obClean);

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
	 * @var String | null : prefix for exposed variables
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
}
