<?php

namespace Transitive\Core;

/**
 * BasicFront class.
 *
 * @implements FrontController
 */
class BasicFront implements FrontController
{
    protected $layout;

    /**
     * @var array Router
     */
    protected $routers;
    protected $route;
    public $obClean;
    protected $obContent;
    protected $executed = false;

    public function __construct()
    {
        $this->obClean = true;
        $this->obContent = '';

        $this->layout = new Route(new Presenter(), new BasicView());

        $this->layout->getView()->content = function ($data) {
            echo $data['view'];
        };
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function getObContent(): string
    {
        return $this->obContent;
    }

    protected function _getRoute(string $query): ?Route
    {
        foreach($this->routers as $router)
            if(null !== ($testRoute = $router->execute($query)))
                return $testRoute;
        throw new RoutingException('No route.');
    }

    public function execute(string $queryURL = null): ?Route
    {
        $exposedVariables = ['binder' => $this];

        if(!isset($this->routers))
            throw new RoutingException('No routeR.');
        else {
            $this->route = $this->_getRoute($queryURL);
            if(isset($this->route))
                $this->obContent = $route->execute($exposedVariables, null, $this->obClean);

            $this->executed = true;

            $content = ['view' => $this->route->getView()];

            $this->layout->getPresenter()->setData($content);
            $this->layout->execute(null, null, $this->obClean);

            return $this->route;
        }
    }

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
            case 'application/vnd.transitive.content+css':
                return $this->getView()->getStylesContent();
            break;
            case 'application/vnd.transitive.content+javascript':
                return $this->getView()->getScriptsContent();
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
            case 'application/json':
                if($this->hasContent('api'))
                    return $this->getContent('api')->asJson();
            break;
            case 'application/xml':
                if($this->hasContent('api'))
                    return $this->getContent('api')->asXML();
            break;

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
     * Set routers list, replacing any previously set Router.
     *
     * @param array $routers
     */
    public function setRouters(array $routers): void
    {
        $this->routers = $routers;
    }

    /**
     * Add specified Router.
     *
     * @param Router $router
     */
    public function addRouter(Router $router): void
    {
        $this->routers[] = $router;
    }

    /**
     * Remove specified Router.
     *
     * @return bool
     *
     * @param Router $router
     *
     * @todo implement this
     */
    public function removeRouter(Router $router): bool
    {
        return false;
    }

    /**
     * Get current Route.
     *
     * @return Route
     */
    public function getRoute(): ?Route
    {
        return $this->route;
    }
}
