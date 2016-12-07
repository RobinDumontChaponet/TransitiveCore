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

		function &includePresenter(Binder &$binder):Presenter
		{
			$presenter = $binder->getPresenter();
// 			$data = array();
			include $binder->getPresenterPath();

			return $presenter;
		}
		function includeView(Binder &$binder, Presenter &$presenter):bool
		{
			if($binder->getViewPath() == null)
				return false;

			$view = $binder->getView();

			$view->setData($presenter->getData());
			unset($presenter);

// 			$view->setJSON($binder->isJSON());

			include $binder->getViewPath();

			return true;
		}

        includeView($this, includePresenter($this));
    }

    /**
     * @param string $key
     */
    public function getContent(string $key = null)
    {
        return $this->getView()->getContent($key);
    }

    /**
     * @param string $key
     */
    public function printContent(string $key = null):void
    {
        $this->getView()->printContent($key);
    }

    public function getHead():ViewRessource
	{
		return $this->getView()->getHead();
	}

    public function getDocument():ViewRessource
	{
		return $this->getView()->getDocument();
	}
	public function printDocument():void
	{
		$this->getView()->printDocument();
	}

	public function printHead():void
	{
		$this->getView()->printHead();
	}

	public function getBody()
    {
		return $this->getView()->getBody();
    }

    public function printBody():void
    {
		$this->getView()->printBody();
    }

    /**
     * @param string $key
     */
    public function print(string $key = null):void
    {
        // TODO: implement here
    }
}
