<?php

namespace Transitive\Core;

if (!function_exists('http_response_code')) {
    function http_response_code($newcode = null) {
        static $code = 200;
        if($newcode !== null) {
            header('X-PHP-Response-Code: '.$newcode, true, $newcode);
            if(!headers_sent())
                $code = $newcode;
        }

        return $code;
    }
}

class FrontController
{
	/**
	 * @var Request
	 */
	private $binder;

	/**
	 * @var array Router
	 */
	public $routers;

	public function __construct(string $queryURL)
	{
		$this->query = (!empty($query)) ? $query : self::$defaultQuery;
		$this->binder = new Binder($this->query, $this->query);
	}

	/**
	 * @return string
	 */
	public function getQuery():string
	{
        return $this->query;
    }

    /**
     * @return string
     */
    public function getBinder():string
    {
        return $this->binder;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query):void
    {
        $this->query = $query;
    }

    /**
     * @param Binder $binder
     */
    public function setBinder(Binder $binder):void
    {
        $this->binder = $binder;
    }

    /**
     * @param bool $isJSON
     */
    public function execute(bool $isJSON = false):void
    {
/*  // @ IN Router/Route
		if(!is_file($this->binder->getPresenterPath())) {
            http_response_code(404);
            $_SERVER['REDIRECT_STATUS'] = 404;

            $this->binder->setPresenterPath('genericHttpErrorHandler.presenter.php');
            if(!is_file(self::$viewIncludePath.'genericHttpErrorHandler.view.php'))
                $this->binder->setViewPath('');
            else
                $this->binder->setViewPath(self::$viewIncludePath.'genericHttpErrorHandler.view.php');
		}
*  IN Router/Route */
		if(!$this->binder->getView()->hasContent()) {
            http_response_code(204);
            $_SERVER['REDIRECT_STATUS'] = 404;
		}
		$this->binder->execute($isJSON);

		if($this->binder->isJSON() && !headers_sent())
			header('Content-Type: application/json');
    }

    public function printMetas():void
    {
        $this->binder->getView()->printMetas();
    }

    /**
     * @param string $prefix
     * @param string $separator
     * @param string $endSeparator
     *
     * @return string
     */
    public function getTitle(string $prefix = '', string $separator = ' | ', string $endSeparator = ''):string
    {
        $title = $this->binder->getView()->getTitle();

        if(!empty($title))
            return $prefix.$separator.$title.$endSeparator;

        return $prefix;

    }

    /**
     * @param string $prefix
     * @param string $separator
     * @param string $endSeparator
     */
    public function printTitle(string $prefix = '', string $separator = ' | ', string $endSeparator = ''):void
    {
        echo '<title>';
        echo $this->getTitle($prefix, $separator, $endSeparator);
        echo '</title>';
    }

    public function printStyles():void
    {
        $this->binder->getView()->printStyles();
    }

    public function printScripts():void
    {
        $this->binder->getView()->printScripts();
    }

    /**
     * @param string $key
     */
    public function displayContent(string $key = null):void
    {
        $this->binder->displayContent($key);
    }

    public function outputJson():void
    {
        if(!headers_sent())
            header('Content-Type: application/json');

        $this->binder->getView()->outputJson();
    }

/*
    public function __debugInfo():void
    {
        // TODO: implement here
    }
*/

    public function __toString():string
    {
        // TODO: implement here
    }

    /**
     * @return array
     */
    public function getRouters():array
    {
		return $this->routers;
    }

    /**
     * @param array $routers
     */
    public function setRouters(array $routers):void
    {
		$this->routers = $routers;
    }

    /**
     * @param Router $router
     */
    public function addRouter(Router $router):void
    {
		$this->routers[] = $router;
    }

    public function removeRouter(Router $router):bool
    {
        // TODO: implement here
    }
}
