<?php

namespace Transitive\Core;

class PathRouter implements Router
{
    /**
     * @var array Route
     */
    private $presentersPath;
    private $viewsPath;
    private $separator;

    public $method;

    public function __construct(string $presentersPath, string $viewsPath = null, string $separator = '/', string $method = 'all')
    {
        $this->presentersPath = $presentersPath;
        $this->viewsPath = $viewsPath ?? $presentersPath;

        $this->method = $method;
        $this->separator = $separator;
    }

    public function execute(string $pattern, string $method = 'all'): ?Route
    {
        if($this->method != $method || empty($pattern))
            return null;

        $presenterPattern = $pattern.'.presenter.php';
        $viewPattern = $pattern.'.view.php';

/*
        $realPresenter = realpath($this->presentersPath . dirname($presenterPattern) .'/');
        $realView = realpath($this->viewsPath . dirname($viewPattern) .'/');

        var_dump($this->presentersPath, basename($presenterPattern), $realPresenter, $realView);

        var_dump($realPresenter.'/'.basename($presenterPattern), strpos($realPresenter, $this->presentersPath));

        if(strpos($realPresenter, $this->presentersPath) === 0 && strpos($realView, $this->viewsPath) === 0)
            return new Route($realPresenter.'/'.basename($presenterPattern), $realView.'/'.basename($viewPattern));
        else
            return null;
*/
        $realPresenter = self::_real($presenterPattern, $this->separator);
        $realView = self::_real($viewPattern, $this->separator);

        if($realPresenter && $realView)
            return new Route($this->presentersPath.$realPresenter, $this->viewsPath.$realView);
        else
            return null;
    }

    private static function _real(string $filename, string $separator = '/') {
        $path = [];
        foreach(explode($separator, $filename) as $part) {
            if (empty($part) || $part === '.')
                continue;

            if ($part !== '..')
                array_push($path, $part);
            elseif (count($path) > 0)
                array_pop($path);
            else
                return false;
        }

        return implode('/', $path);
    }
}
