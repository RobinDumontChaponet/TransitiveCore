<?php
namespace Transitive\Core;
function getBestSupportedMimeType($mimeTypes = null) {
    // Values will be stored in this array
    $acceptTypes = array();
    // divide it into parts in the place of a ","
    $accept = explode(',', strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT'])));
    foreach ($accept as $a) {
        // the default quality is 1.
        $q = 1;
        // check if there is a different quality
        if (strpos($a, ';q=')) {
            // divide "mime/type;q=X" into two parts: "mime/type" i "X"
            list($a, $q) = explode(';q=', $a);
        }
        // mime-type $a is accepted with the quality $q
        // WARNING: $q == 0 means, that mime-type isn’t supported!
        $acceptTypes[$a] = $q;
    }
    arsort($acceptTypes);
    // if no parameter was passed, just return parsed data
    if (!$mimeTypes) return $acceptTypes;
    $mimeTypes = array_map('strtolower', (array) $mimeTypes);
    // let’s check our supported types:
    foreach ($acceptTypes as $mime => $q) {
       if ($q && in_array($mime, $mimeTypes)) return $mime;
    }
    // no mime-type found
    return null;
}
class FrontController
{
    public $test = 0;
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
    public static $mimeTypes = array(
        'application/xhtml+xml', 'text/html',
        'application/json', 'application/xml',
        'application/vnd.transitive.content+xhtml', 'application/vnd.transitive.content+html',
        'application/vnd.transitive.content+css', 'application/vnd.transitive.content+javascript',
        'application/vnd.transitive.content+json', 'application/vnd.transitive.content+xml', 'application/vnd.transitive.content+yaml',
        'application/vnd.transitive.head+json', 'application/vnd.transitive.head+xml', 'application/vnd.head+yaml',
        'application/vnd.transitive.document+json', 'application/vnd.transitive.document+xml', 'application/vnd.transitive.document+yaml',
    );
    public function __construct()
    {
        $this->contentType = getBestSupportedMimeType(self::$mimeTypes);
        $this->obClean = true;
        $this->obContent = '';
        $cwd = dirname(getcwd()).'/';
        $this->layout = function () { ?>
<!DOCTYPE html>
<!--[if lt IE 7]><html class="lt-ie9 lt-ie8 lt-ie7" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<!--[if IE 7]>   <html class="lt-ie9 lt-ie8" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<!--[if IE 8]>   <html class="lt-ie9" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<!--[if gt IE 8]><html class="get-ie9" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<head>
<meta charset="UTF-8">
<?php $this->printMetas() ?>
<?php $this->printTitle('Default layout') ?>
<?php $this->printStyles() ?>
<?php $this->printScripts() ?>
</head>
<body>
<?php $this->printContent(); ?>
</body>
</html>
<?php  };
}
    public function getContentType(): ?string
    {
        return $this->contentType;
    }
    public function isAPI(): string
    {
        return $this->getContentType() == 'application/json' || $this->getContentType() == 'application/xml';
    }
    public function getRequestMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
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
        if(empty($queryURL))
            $queryURL = 'genericHttpErrorHandler';
        if(!isset($this->routers))
            throw new RoutingException('No routeR.');
        else {
            $this->route = $this->_getRoute($queryURL);
//             try {
                $this->obContent = $this->route->execute($this);
/*
            } catch(RoutingException $e) {
                try {
                    $this->obContent = $this->httpErrorRoute->execute($this);
                } catch (RoutingException $e) {
                    $this->obContent = self::$defaultHttpErrorRoute->execute($this);
                    throw $e;
                }
                throw $e;
            }
*/
            $this->executed = true;
            ++$this->test;
            if(!empty($this->contentType)) {
                header('Content-Type: '.$this->contentType);
                if(!in_array($this->contentType, array('application/xhtml+xml', 'text/html'))) {
                    header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
                    header('Cache-Control: public, max-age=60');
                }
            }
            header('Vary: X-Requested-With,Content-Type');
            if($this->hasView() && !$this->getView()->hasContent()) {
                throw RoutingException('No content', 204);
                return false;
            }
            return true;
        }
    }
/*
    public function printMetas(): void
    {
	    if(isset($this->route->view))
	        $this->route->view->printMetas();
    }
*/
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
/*
    public function printStyles(): void
    {
		if(isset($this->route->view))
	        $this->route->view->printStyles();
    }
*/
/*
    public function printScripts(): void
    {
	    if(isset($this->route->view))
	        $this->route->view->printScripts();
    }
*/
    /**
     * @param string $key
     */
    public function hasContent(string $key = null): bool
    {
        return $this->view->hasContent($key);
    }
    /**
     * @param string $key
     */
    public function getContent(string $key = null)
    {
        return $this->view->getContent($key);
    }
    /**
     * @param string $key
     */
    public function printContent(string $key = null): void
    {
	    if(isset($this->route->view))
        	$this->route->view->printContent($key);
    }
    public function getHead(): ViewRessource
    {
        return $this->view->getHead();
    }
    public function printHead(): void
    {
        $this->view->printHead();
    }
    public function getBody()
    {
        return $this->view->getBody();
    }
    public function printBody(): void
    {
        $this->view->printBody();
    }
    public function getDocument()
    {
        return $this->view->getDocument();
    }
    public function printDocument(): void
    {
        $this->view->printDocument();
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
                echo $this->view->getStylesContent();
            break;
            case 'application/vnd.transitive.content+javascript':
                echo $this->view->getScriptsContent();
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
    public function redirect($url, $delay = 0, $code = 303) {
        if(isset($this->view))
            $this->view->addRawMetaTag('<meta http-equiv="refresh" content="'.$delay.'; url='.$url.'">');
        else {
            $this->executed = true;
            $this->view = new View();
        }
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
    public function goBack() {
        if(isset($_SESSION['referrer'])) {
            $this->redirect($_SESSION['referrer']);
            return true;
        } else
            return false;
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
}
