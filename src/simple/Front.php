<?php

namespace Transitive\Simple;

use Transitive\Core;
use Transitive\Routing;

/**
 * SimpleFront.
 *
 * @implements Routing\FrontController
 */
class Front implements Routing\FrontController
{
    const defaultViewClassName = '\Transitive\Simple\View';

    /**
     * Layout route.
     *
     * @var Route
     */
    protected $layout;

    /**
     * List of Routing\Router.
     *
     * @var array Routing\Router
     */
    protected $routers;

    /**
     * Current route.
     *
     * @todo remove this ?
     *
     * @var Route
     */
    protected $route;

    /**
     * Should presenter & view 's buffer be cleaned ?
     *
     * @var bool
     */
    public $obClean;

    /**
     * content presenter & view 's buffer if obClean is set to true.
     *
     * @var string
     */
    protected $obContent;

    /**
     * did execute run successfuly ?
     *
     * @var bool
     */
    protected $executed = false;

    public function __construct()
    {
        $this->obClean = true;
        $this->obContent = '';

        $this->layout = new Routing\Route(new Core\Presenter(), new View());

        $this->layout->getView()->content = function ($data) {
            echo $data['view'];
        };
    }

    /*
     * @todo remove this ?
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * Get presenter & view buffer (if obClean is enabled).
     *
     * @return string
     */
    public function getObContent(): string
    {
        return $this->obContent;
    }

    protected function _getRoute(string $query, string $defaultViewClassName = null): ?Routing\Route
    {
        if(!isset($this->routers))
            throw new Routing\RoutingException('No routeR.', 404);
        else {
            foreach($this->routers as $router) {
                if(!$router->hasDefaultViewClassName())
                    $router->setDefaultViewClassName($defaultViewClassName);
                if(null !== ($testRoute = $router->execute($query)))
                    return $testRoute;
            }

            throw new Routing\RoutingException('No route.', 404);
        }
    }

    public function execute(string $queryURL = null): ?Routing\Route
    {
        $this->route = $this->_getRoute($queryURL, self::defaultViewClassName);
        if(isset($this->route))
            try {
                $this->obContent = $this->route->execute($this->obClean);
            } catch(Core\BreakFlowException $e) {
                $this->execute($e->getQueryURL());
            }

        $this->executed = true;

        $content = ['view' => $this->route->getView()];

        $this->layout->getPresenter()->setData($content);
        $this->layout->execute($this->obClean);

        return $this->route;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo()
    {
        return [
            'routers' => $this->routers,
            'route' => $this->route,
            'obClean' => $this->obClean,
            'obContent' => $this->obContent,
            'executed' => $this->executed,
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function __toString(): string
    {
        return $this->getContent();
    }

    /**
     * Return processed content from current route.
     *
     * @return string
     *
     * @param string $contentType = null
     */
    public function getContent(string $contentType = null): string
    {
/*
        if(null == $contentType)
            $contentType = $this->contentType;
*/
        switch($contentType) {
            case 'application/vnd.transitive.document+json':
                return $this->getDocument();
            break;
            case 'application/vnd.transitive.document+xml':
                return $this->getDocument()->asXML('document');
            break;
            case 'application/vnd.transitive.document+yaml':
                return $this->getDocument()->asYAML();
            break;
            case 'application/vnd.transitive.head+json':
                return $this->getHead()->asJson();
            break;
            case 'application/vnd.transitive.head+xml':
                return $this->getHead()->asXML('head');
            break;
            case 'application/vnd.transitive.head+yaml':
                return $this->getHead()->asYAML();
            break;
            case 'application/vnd.transitive.content+xhtml': case 'application/vnd.transitive.content+html':
                return $this->getContent();
            break;
            case 'application/vnd.transitive.content+json':
                return $this->getContent()->asJson();
            break;
            case 'application/vnd.transitive.content+xml':
                return $this->getContent()->asXML('content');
            break;
            case 'application/vnd.transitive.content+yaml':
                return $this->getContent()->asYAML();
            break;

            case 'text/plain':
                return $this->getContent()->asString();
            break;
/*
            case 'application/json':
                if($this->hasContent('api'))
                    return $this->getContent('api')->asJson();
            break;
            case 'application/xml':
                if($this->hasContent('api'))
                    return $this->getContent('api')->asXML();
            break;
*/

            default:
                return $this->layout->getView();
        }
    }

    /**
     * Get all routers.
     *
     * @return array
     */
    public function getRouters(): array
    {
        return $this->routers;
    }

    /**
     * Set routers list, replacing any previously set router.
     *
     * @param array $routers
     */
    public function setRouters(array $routers): void
    {
        $this->routers = $routers;
    }

    /**
     * Add specified router.
     *
     * @param Router $router
     */
    public function addRouter(Routing\Router $router): void
    {
        $this->routers[] = $router;
    }

    /**
     * Remove specified router
     * return true at success and false otherwise.
     *
     * @return bool
     *
     * @param Routing\Router $router
     *
     * @todo implement this
     */
    public function removeRouter(Routing\Router $router): bool
    {
        return false;
    }

    /**
     * Return current Route.
     *
     * @todo remove this ?
     *
     * @return Route
     */
    public function getRoute(): ?Routing\Route
    {
        return $this->route;
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
