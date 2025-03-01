<?php

namespace Transitive\Simple;

use Transitive\Core;
use Transitive\Routing;

/**
 * SimpleFront.
 */
class Front implements Routing\FrontController
{
    const defaultViewClassName = '\Transitive\Simple\View';

    /**
     * Layout route.
     */
    protected ?Routing\Route $layout;

    /**
     * List of Routing\Router.
     */
    protected array $routers = [];

    /**
     * Current route.
     *
     * @todo remove this ?
     */
    protected ?Routing\Route $route = null;

    /**
     * Should presenter & view 's buffer be cleaned ?
     */
    public bool $obClean = true;

    /**
     * content presenter & view 's buffer if obClean is set to true.
     */
    protected string $obContent = '';

    /**
     * did execute run successfully ?
     */
    protected bool $executed = false;

    public function __construct()
    {
        $this->obContent = '';

        $this->layout = new Routing\Route(new Core\Presenter(), new View());

        $this->setLayoutContent(function (array $data) {
            echo $data['view'];
        });
    }

    /**
     * Get presenter & view buffer (if obClean is enabled).
     */
    public function getObContent(): string
    {
        return $this->obContent;
    }

    protected function _getRoute(string $query, ?string $defaultViewClassName = null): ?Routing\Route
    {
        if(empty($this->routers))
            throw new Routing\RoutingException('No routeR.', 404, $query);
        else {
            foreach($this->routers as $router) {
                if(!$router->hasDefaultViewClassName())
                    $router->setDefaultViewClassName($defaultViewClassName);
                if(null !== ($testRoute = $router->execute($query)))
                    return $testRoute;
            }

            throw new Routing\RoutingException('No route.', 404, $query);
        }
    }

    public function execute(string $queryURL = ''): ?Routing\Route
    {
        $this->route = $this->_getRoute($queryURL, self::defaultViewClassName);
        if(isset($this->route))
            try {
                $this->obContent = $this->route->execute($this->obClean);
            } catch(Core\BreakFlowException $e) {
                return $this->execute($e->getQueryURL());
            }

        $this->executed = true;

        $layout = $this->layout;
		if(isset($layout)) {
			$presenter = $layout->getPresenter();
			if($presenter instanceof Core\Presenter) {
				$presenter->add('view', $this->route?->getView());
			}
        	$layout->execute($this->obClean);
		}

        return $this->route;
    }

    public function save(?string $path = null): int
    {
        if(empty($path))
            $path = getcwd().'/../compiled';

        $savedCount = 0;

        $routes = [];
        foreach($this->routers as $router)
            $routes += $router->getRoutes();

        $requests = array_keys($routes);

        foreach($requests as $request) {
            $route = $this->execute($request);

            if($route) {
// 				echo $request, ' [done]';

                if(false !== file_put_contents($path.'/json/'.urlencode($request).'.json', $route->getAllDocument()?->asJSON ?? '')) {
                    ++$savedCount;
// 					echo $request, ' [saved json]';
                }

                if(false !== file_put_contents($path.'/html/'.urlencode($request).'.html', $this->getContent())) {
                    ++$savedCount;
// 					echo $request, ' [saved html]';
                }
            }
        }

        return $savedCount;
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
     */
    public function getContent(string $contentType = ''): string
    {
/*
        if(null == $contentType)
            $contentType = $this->contentType;
*/
// 		if(empty($this->route)) {
// 			http_response_code(404);
// 			$_SERVER['REDIRECT_STATUS'] = 404;
//
// 			return '';
// 		}

        switch($contentType) {
            case 'application/vnd.transitive.document+json':
                return (string) $this->route?->getDocument();
            break;
            case 'application/vnd.transitive.document+xml':
                return $this->route?->getDocument()->asXML('document');
            break;
            case 'application/vnd.transitive.document+yaml':
                return $this->route?->getDocument()->asYAML();
            break;
            case 'application/vnd.transitive.head+json':
                return $this->route?->getHead()->asJson();
            break;
            case 'application/vnd.transitive.head+xml':
                return $this->route?->getHead()->asXML('head');
            break;
            case 'application/vnd.transitive.head+yaml':
                return $this->route?->getHead()->asYAML();
            break;
            case 'application/vnd.transitive.content+xhtml': case 'application/vnd.transitive.content+html':
                return (string) $this->route?->getContent();
            break;
            case 'application/vnd.transitive.content+json':
                return $this->route?->getContent()?->asJson() ?? '';
            break;
            case 'application/vnd.transitive.content+xml':
                return $this->route?->getContent()?->asXML('content') ?? '';
            break;
            case 'application/vnd.transitive.content+yaml':
                return $this->route?->getContent()?->asYAML() ?? '';
            break;

            case 'text/plain':
                return $this->layout?->getContent()?->asString() ?? '';
            break;

            case 'application/json':
                if($this->route?->hasContent('application/json'))
                    return $this->route->getContentByType('application/json')?->asJson() ?? '';
                elseif(404 != http_response_code()) {
                    http_response_code(404);
                    $_SERVER['REDIRECT_STATUS'] = 404;
                }
                return '';
            break;
            case 'application/xml':
                if($this->route?->hasContent('application/xml'))
                    return $this->route->getContentByType('application/xml')?->asJson() ?? '';
                elseif(404 != http_response_code()) {
                    http_response_code(404);
                    $_SERVER['REDIRECT_STATUS'] = 404;
                }

                return '';
            break;

            default:
                return (string) $this->layout?->getContent();
        }
    }

    /**
     * Get all routers.
     */
    public function getRouters(): array
    {
        return $this->routers;
    }

    /**
     * Set routers list, replacing any previously set router.
     */
    public function setRouters(array $routers): void
    {
        $this->routers = $routers;
    }

    /**
     * Add specified router.
     */
    public function addRouter(Routing\Router $router): void
    {
        $this->routers[] = $router;
    }

    /**
     * Remove specified router
     * return true at success and false otherwise.
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
     */
    public function getRoute(): ?Routing\Route
    {
        return $this->route;
    }

    public function setLayout(Routing\Route $layout): void
    {
        $this->layout = $layout;
    }

    public function setLayoutContent(mixed $content, string $contentType = ''): bool
    {
        if(isset($this->layout)) {
			if(!empty($content)) {
				$view = $this->layout->getView();

				if(isset($view) && $view instanceof View) {
            		$view->addContent($content, $contentType);
				}
			}

            return true;
        }

        return false;
    }
}
