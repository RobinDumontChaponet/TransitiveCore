<?php

namespace Transitive\Core;

interface FrontController
{
//     public function getContentType(): ?string;

//     public function getRequestMethod(): string;
    public function getObContent(): string;

    /**
     * @return Presenter
     */
    public function getPresenter(): Presenter;

    /**
     * @return bool
     */
    public function hasView(): bool;

    /**
     * @return View
     */
    public function getView(): ?View;

    public function execute(string $queryURL = null): ?Route;

    /**
     * @param string $prefix
     * @param string $separator
     * @param string $endSeparator
     *
     * @return string
     */
    public function getTitle(string $prefix = '', string $separator = ' | ', string $endSeparator = ''): string;

    /**
     * @param string $prefix
     * @param string $separator
     * @param string $endSeparator
     */
    public function printTitle(string $prefix = '', string $separator = ' | ', string $endSeparator = ''): void;

    /**
     * @param string $key
     */
    public function hasContent(string $key = null): bool;

    /**
     * @param string $key
     */
    public function getContent(string $key = null);

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
    public function getHead(): ViewResource;

    public function printHead(): void;

    public function getBody();

    public function printBody(): void;

    public function getDocument();

    public function printDocument(): void;

/*
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
*/
    /**
     * @return array
     */
    public function getRouters(): array;

    /**
     * @param array $routers
     */
    public function setRouters(array $routers): void;

    /**
     * @param Router $router
     */
    public function addRouter(Router $router): void;

    public function removeRouter(Router $router): bool;

    public function getRoute(): ?Route;
}
