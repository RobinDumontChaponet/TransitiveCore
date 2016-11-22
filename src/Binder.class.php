<?php

namespace Transitive\Core;

class Binder
{
    /**
     * @var string
     */
    private $presenterPath;

    /**
     * @var string
     */
    private $viewPath;

    /**
     * @var Presenter
     */
    private $presenter;

    /**
     * @var View
     */
    private $view;

    /**
     * @var bool
     */
    private $JSON;

    public function __construct(string $presenterPath = '', string $viewPath = '')
    {
	    $this->setPresenterPath($presenterPath);
        $this->setViewPath($viewPath);
        $this->presenter = new Presenter();
        $this->view = new View();
    }

	/**
	 * @return bool
	 */
	public function isJSON():bool
	{
        return $this->JSON;
    }

    /**
     * @param bool $isJSON
     */
    public function setJSON(bool $isJSON = true):void
    {
        $this->JSON = $isJSON;
    }

    /**
     * @param string $path
     */
    public function setPresenterPath(string $path):void
    {
		$this->presenterPath = $path.'.presenter.php';
    }

    /**
     * @param string $path
     */
    public function setViewPath(string $path):void
    {
		$this->viewPath = $path.'.view.php';
    }

    /**
     * @return string
     */
    public function getPresenterPath():string
    {
		return $this->presenterPath;
    }

    /**
     * @return string
     */
    public function getViewPath():string
    {
		return $this->viewPath;
    }

    /**
     * @return Presenter
     */
    public function getPresenter():Presenter
    {
		return $this->presenter;
    }

    /**
     * @return View
     */
    public function getView():View
    {
		return $this->view;
    }

    /**
     * @param bool $isJSON
     */
    public function execute(bool $isJSON = false):void
    {
	    $this->setJSON($isJSON);

		function &includePresenter(Binder &$binder):array
		{
			$presenter = $self->getPresenter();
// 			$data = array();
			include $self->getPresenterPath();

			return $presenter;
		}
		function includeView(Binder &$binder, Presenter &$presenter):bool
		{
			if($self->getViewPath() == null)
				return false;

			$view = $self->getView();

			$view->setData($presenter->getData());
			unset($presenter);

			$view->setJSON($self->isJSON());

			include $self->getViewPath();

			return true;
		}

        includeView($this, includePresenter($this));
    }

    /**
     * @param string $key
     */
    public function displayContent(string $key = null):void
    {
		if($this->JSON)
			$this->getView()->displayJSONContent($key);
		else
			$this->getView()->displayHTMLContent($key);
    }

    /**
     * @param string $key
     */
    public function print(string $key = null):void
    {
        // TODO: implement here
    }
}
