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

/**
 * WebFront class.
 *
 * @extends BasicFront
 * @implements FrontController
 */
class WebFront extends BasicFront implements FrontController
{
    private $httpErrorRoute;
    private static $defaultHttpErrorRoute;

    private $contentType;
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

        $this->layout = new Route(new Presenter(), new BasicView());

        $this->layout->getView()->content = function ($data) { ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<?php $data['view']->printMetas(); ?>
<?php $data['view']->printTitle('Default layout'); ?>
<?php $data['view']->printStyles(); ?>
<?php $data['view']->printScripts(); ?>
</head>
<body>
<?= $data['view']; ?>
</body>
</html>
<?php  };
}

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

/*
    public function isAPI(): string
    {
        return 'application/json' == $this->getContentType() || 'application/xml' == $this->getContentType();
    }
*/

    public function getRequestMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function execute(string $queryURL = null): ?Route
    {
        $this->contentType = getBestSupportedMimeType(self::$mimeTypes);

        $exposedVariables = ['binder' => $this];

        if(!isset($this->routers))
            throw new RoutingException('No routeR.');
        else {
            $routes = [$this->_getRoute($queryURL), $this->httpErrorRoute, self::$defaultHttpErrorRoute];
            foreach($routes as $route) {
                if(isset($route))
                    try {
                        $this->obContent = $route->execute($exposedVariables, [], $this->obClean);
                        $this->route = $route;
/*
                        unset($routes);
                        unset($route);
*/

                        break;
                    } catch(RoutingException $e) {
                        if($e->getCode() > 200) {
                            http_response_code($e->getCode());
                            $_SERVER['REDIRECT_STATUS'] = $e->getCode();
                        }
                        continue;
                    }
            }
            $this->executed = true;

            if($this->route->hasView() && !$this->route->getView()->hasContent()) {
                http_response_code(204);
                $_SERVER['REDIRECT_STATUS'] = 204;
            }
            if(!empty($this->contentType)) {
                header('Content-Type: '.$this->contentType);
                if(!in_array($this->contentType, array('application/xhtml+xml', 'text/html'))) {
                    header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
                    header('Cache-Control: public, max-age=60');
                }
            }
            header('Vary: X-Requested-With,Content-Type');

            $content = ['view' => $this->route->getView()];

            $this->layout->getPresenter()->setData($content);
            $this->layout->execute(null, null, $this->obClean);

            return $this->route;
        }
    }

	/**
	 * @codeCoverageIgnore
	 */
    public function __debugInfo()
    {
        return parent::__debugInfo()
        +[
            'httpErrorRoute' => $this->httpErrorRoute,
        ];
    }

	/**
	 * @codeCoverageIgnore
	 */
    public function __toString(): string
    {
        return $this->getContent();
    }

    public function getContent(string $contentType = null): string
    {
        if(null == $contentType)
            $contentType = $this->contentType;
        switch($contentType) {
            case 'application/vnd.transitive.document+json':
                return $this->route->getDocument();
            break;
            case 'application/vnd.transitive.document+xml':
                return $this->route->getDocument()->asXML('document');
            break;
            case 'application/vnd.transitive.document+yaml':
                return $this->route->getDocument()->asYAML();
            break;
            case 'application/vnd.transitive.head+json':
                return $this->route->getHead()->asJson();
            break;
            case 'application/vnd.transitive.head+xml':
                return $this->route->getHead()->asXML('head');
            break;
            case 'application/vnd.transitive.head+yaml':
                return $this->route->getHead()->asYAML();
            break;
            case 'application/vnd.transitive.content+xhtml': case 'application/vnd.transitive.content+html':
                return $this->route->getContent();
            break;
            case 'application/vnd.transitive.content+css':
                return $this->route->getView()->getStylesContent();
            break;
            case 'application/vnd.transitive.content+javascript':
                return $this->route->getView()->getScriptsContent();
            break;
            case 'application/vnd.transitive.content+json':
                return $this->route->getContent()->asJson();
            break;
            case 'application/vnd.transitive.content+xml':
                return $this->route->getContent()->asXML('content');
            break;
            case 'application/vnd.transitive.content+yaml':
                return $this->route->getContent()->asYAML();
            break;
/*
            case 'application/json':
                if($this->hasContent('api'))
                    echo $this->getContent('api')->asJson();
            break;
*/
/*
            case 'application/xml':
                if($this->hasContent('api'))
                    echo $this->getContent('api')->asXML();
            break;
*/
            default:
                return $this->layout->getView();
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

    public static function setDefaultHttpErrorRoute(Route $route): void
    {
        self::$defaultHttpErrorRoute = $route;
    }

    public function setHttpErrorRoute(Route $route): void
    {
        $this->httpErrorRoute = $route;
    }

    public function setLayoutContent($content = null): bool
    {
        if(isset($this->layout) && $this->layout->hasView()) {
            $this->layout->getView()->content = $content;

            return true;
        }

        return false;
    }
}

WebFront::setDefaultHttpErrorRoute(new Route(dirname(getcwd()).'/presenters/genericHttpErrorHandler.php', dirname(getcwd()).'/views/genericHttpErrorHandler.php'));
