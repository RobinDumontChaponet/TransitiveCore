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

function getBestSupportedMimeType($mimeTypes = null) {
    // Values will be stored in this array
    $AcceptTypes = array();

    // Accept header is case insensitive, and whitespace isn’t important
    $accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
    // divide it into parts in the place of a ","
    $accept = explode(',', $accept);
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
        $AcceptTypes[$a] = $q;
    }
    arsort($AcceptTypes);

    // if no parameter was passed, just return parsed data
    if (!$mimeTypes) return $AcceptTypes;

    $mimeTypes = array_map('strtolower', (array) $mimeTypes);

    // let’s check our supported types:
    foreach ($AcceptTypes as $mime => $q) {
       if ($q && in_array($mime, $mimeTypes)) return $mime;
    }
    // no mime-type found
    return null;
}

class FrontController
{
    /**
     * @var Presenter
     */
    private $presenter;

    /**
     * @var View
     */
    private $view;

    /**
     * @var array Router
     */
    private $routers;
    private $route;

    public $layout;

    private $contentType;

    public $obClean;
    private $obContent;

    public static $mimeTypes = array(
        'application/xhtml+xml', 'text/html',
        'application/json',
        'application/vnd.transitive.document+json', 'application/vnd.transitive.document+xml', 'application/vnd.transitive.document+yaml',
        'application/vnd.transitive.head+json', 'application/vnd.transitive.head+xml', 'application/vnd.head+yaml',
        'application/vnd.transitive.content+json', 'application/vnd.transitive.content+xml', 'application/vnd.transitive.content+yaml',
    );

    public function __construct()
    {
        $this->contentType = getBestSupportedMimeType(self::$mimeTypes);

        $this->obClean = true;
        $this->obContent = '';

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
<base href="<?php echo (constant('SELF') == null) ? '/' : constant('SELF').'/'; ?>" />
<?php $this->printStyles() ?>
<?php $this->printScripts() ?>
</head>
<body>
<?php $this->printContent(); ?>
</body>
</html>

<?php  };
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
        return $this->presenter;
    }

    /**
     * @return View
     */
    public function getView(): View
    {
        return $this->view;
    }

    public function execute(string $queryURL = null): bool
    {
        $queryURL = (!empty($queryURL)) ? $queryURL : 'index';

        function includePresenter(FrontController &$binder, string $path, Route $route)
        {
            $presenter = $binder->getPresenter();
// 			$data = array();

            if($binder->obClean) {
                ob_start();
                ob_clean();

                include $path;

                return ob_get_clean();
            } else
                include $path;
        }

        function includeView(FrontController &$binder, string $path)
        {
            if($path === null)
                return;

            $view = $binder->getView();

            if(is_file($path))
                if($binder->obClean) {
                    ob_start();
                    ob_clean();

                    include $path;

                    return ob_get_clean();
                } else
                    include $path;
        }

        function noContent() {
            http_response_code(204);
            $_SERVER['REDIRECT_STATUS'] = 404;
        }

        function notFound() {
            http_response_code(404);
            $_SERVER['REDIRECT_STATUS'] = 404;
        }

        if(!isset($this->routers)) {
            notFound();

            echo 'No router.';
            throw new \Exception('No routeR.');
            return false;
        } else {
            foreach($this->routers as $router) {
                if(($testRoute = $router->execute($queryURL)) !== false)
                    $this->route = $testRoute;
            }
            if(!isset($this->route)) {
                notFound();

                echo 'No route.';
                throw new \Exception('No route.');
                return false;
            }

            if(is_string($this->route->presenter)) {
                if(!is_file($this->route->presenter)) {
                    notFound();

                    $this->route->presenter = ROOT_PATH.'/presenters/genericHttpErrorHandler.presenter.php';
                    if(!is_file($this->route->presenter))
                        $this->route->view = '';
                }

                $this->presenter = new Presenter();
                $this->obContent.= includePresenter($this, $this->route->presenter, $this->route);
            } elseif(get_class($this->route->view) == 'Presenter')
                $this->presenter = $this->route->presenter;

            if(is_string($this->route->view)) {
                $this->view = new View();
                $this->view->setData($this->presenter->getData());
                $this->obContent.= includeView($this, $this->route->view);
            } elseif(get_class($this->route->view) == 'View') {
                $this->view = $this->route->view;
                $this->view->setData($this->presenter->getData());
            }

            if(!empty($this->contentType)) {
                header('Content-Type: '.$this->contentType);

                if(!in_array($this->contentType, array('application/xhtml+xml', 'text/html'))) {
                    header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
                    header('Cache-Control: public, max-age=60');
                }
            }

            header('Vary: X-Requested-With,Content-Type');

            if(!$this->getView()->hasContent()) {
                noContent();

                return false;
            }

            return true;
        }
    }

    public function printMetas(): void
    {
        $this->view->printMetas();
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
        echo '<title>';
        echo $this->view->getTitle($prefix, $separator, $endSeparator);
        echo '</title>';
    }

    public function printStyles(): void
    {
        $this->view->printStyles();
    }

    public function printScripts(): void
    {
        $this->view->printScripts();
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
        $this->view->printContent($key);
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
                echo '{"case":"json"}';
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

    public function redirect($url, $delay = 0) {
        if(!headers_sent() && $delay <= 0) {
            header('Location: '.$url);
            return true;
        } else {
            $this->view->addRawMetaTag('<meta http-equiv="refresh" content="'.$delay.'; url='.$url.'">');
            return false;
        }
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
