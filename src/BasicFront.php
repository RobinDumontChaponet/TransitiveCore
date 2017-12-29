<?php
namespace Transitive\Core;

class BasicFront implements FrontController
{
    /**
     * @var array Router
     */
    private $routers;
    private $route;
    public $layout;
    private $contentType;
    public $obClean;
    private $obContent;
    private $executed = false;

    public function __construct()
    {
//         $this->contentType = getBestSupportedMimeType(self::$mimeTypes);
        $this->obClean = true;
        $this->obContent = '';
        $cwd = dirname(getcwd()).'/';
}
    public function getContentType(): ?string
    {
        return $this->contentType;
    }
    public function isAPI(): string
    {
        return $this->getContentType() == 'application/json' || $this->getContentType() == 'application/xml';
    }

    public function getObContent(): string
    {
        return $this->obContent;
    }
    /**
     * @return Presenter
     */
    public function getPresenter(): Presenter
    {
        return $this->route->getPresenter();
    }
    /**
     * @return bool
     */
    public function hasView(): bool
    {
        return isset($this->view);
    }
    /**
     * @return View
     */
    public function getView(): ?View
    {
        return $this->route->getView();
    }
    private function _getRoute(string $query): ?Route
    {
        foreach($this->routers as $router)
            if(($testRoute = $router->execute($query)) !== null)
                return $testRoute;
        throw new RoutingException('No route.');
    }
    public function execute(string $queryURL = null): bool
    {
/*
        if(empty($queryURL))
            $queryURL = 'genericHttpErrorHandler';
*/
        if(!isset($this->routers))
            throw new RoutingException('No routeR.');
        else {
			$this->route = $this->_getRoute($queryURL);
	        if(isset($this->route))
				$this->obContent = $this->route->execute($this);

            $this->executed = true;

            return true;
        }
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
        $title = (isset($this->route->view))? $this->route->view->getTitle() : '';
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
	    if(isset($this->route->view))
	        return $this->route->view->hasContent($key);
    }
    /**
     * @param string $key
     */
    public function getContent(string $key = null)
    {
	    if(isset($this->route->view))
	        return $this->route->view->getContent($key);
    }
    /**
     * @param string $key
     */
/*
    public function printContent(string $key = null): void
    {
	    if(isset($this->route->view))
        	$this->route->view->printContent($key);
    }
*/
    public function getHead(): ViewResource
    {
	    if(isset($this->route->view))
	        return $this->route->view->getHeadValue();
    }
    public function printHead(): void
    {
	    if(isset($this->route->view))
	        $this->route->view->printHead();
    }
    public function getBody()
    {
	    if(isset($this->route->view))
	        return $this->route->view->getBody();
    }
    public function printBody(): void
    {
	    if(isset($this->route->view))
        	$this->route->view->printBody();
    }
    public function getDocument()
    {
	    if(isset($this->route->view))
	        return $this->route->view->getDocument();
    }
    public function printDocument(): void
    {
	    if(isset($this->route->view))
	        $this->route->view->printDocument();
    }
/*
    public function __debugInfo():void
    {
        // TODO: implement here
    }
*/
    public function __toString(): string
    {
        ob_start();
        ob_clean();
        $this->print();
        return ob_get_clean();
    }
    public function print($contentType = null): void
    {
        if($contentType == null)
            $contentType = $this->contentType;
        switch($contentType) {
            case 'application/vnd.transitive.document+json':
                echo $this->getDocument();
            break;
            case 'application/vnd.transitive.document+xml':
                echo $this->getDocument()->asXML('document');
            break;
            case 'application/vnd.transitive.document+yaml':
                echo $this->getDocument()->asYAML();
            break;
            case 'application/vnd.transitive.head+json':
                echo $this->getHead()->asJson();
            break;
            case 'application/vnd.transitive.head+xml':
                echo $this->getHead()->asXML('head');
            break;
            case 'application/vnd.transitive.head+yaml':
                echo $this->getHead()->asYAML();
            break;
            case 'application/vnd.transitive.content+xhtml': case 'application/vnd.transitive.content+html':
                echo $this->getContent();
            break;
            case 'application/vnd.transitive.content+css':
                echo $this->getView()->getStylesContent();
            break;
            case 'application/vnd.transitive.content+javascript':
                echo $this->getView()->getScriptsContent();
            break;
            case 'application/vnd.transitive.content+json':
                echo $this->getContent()->asJson();
            break;
            case 'application/vnd.transitive.content+xml':
                echo $this->getContent()->asXML('content');
            break;
            case 'application/vnd.transitive.content+yaml':
                echo $this->getContent()->asYAML();
            break;
            case 'application/json':
                if($this->hasContent('api'))
                    echo $this->getContent('api')->asJson();
            break;
            case 'application/xml':
                if($this->hasContent('api'))
                    echo $this->getContent('api')->asXML();
            break;
            default:
                switch(gettype($layout = $this->layout)) {
                    case 'string': case 'integer': case 'double':
                        echo $layout;
                    break;
                    case 'object':
                        if(get_class($layout) == 'Closure')
                            $layout($this);
                    break;
                    default:
                        echo 'No Layout';
                }
        }
    }

    /**
     * @return array
     */
    public function getRouters(): array
    {
        return $this->routers;
    }
    /**
     * @param array $routers
     */
    public function setRouters(array $routers): void
    {
        $this->routers = $routers;
    }
    /**
     * @param Router $router
     */
    public function addRouter(Router $router): void
    {
        $this->routers[] = $router;
    }
    public function removeRouter(Router $router): bool
    {
        // TODO: implement here
    }

    public function getRoute(): ?Route
    {
	    return $this->route;
    }
}
