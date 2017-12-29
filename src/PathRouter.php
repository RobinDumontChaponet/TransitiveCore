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

    private $presenterSuffix = '.php';
    private $viewSuffix = '.php';

    public $method;

    public function __construct(string $presentersPath, string $viewsPath = null, string $separator = '/', string $method = 'all')
    {
        $this->presentersPath = $presentersPath;
        $this->presentersPath .= (substr($presentersPath, -1) != '/') ? '/' : '';
        $this->viewsPath = $viewsPath ?? $presentersPath;
        $this->viewsPath .= (substr($viewsPath, -1) != '/') ? '/' : '';

        $this->method = $method;
        $this->separator = $separator;
    }

    public function execute(string $pattern, string $method = 'all'): ?Route
    {
        if($this->method != $method || empty($pattern))
            return null;

        $presenterPattern = $pattern.$this->presenterSuffix;
        $viewPattern = $pattern.$this->viewSuffix;

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

    public function getRoutes(): array
    {
        $array = array();

        foreach(array_diff(scandir($this->presentersPath), array('..', '.', '.DS_Store')) as $pattern) {
            $pattern = substr($pattern, 0, strpos($pattern, $this->presenterSuffix));

            $array[$pattern] = $this->execute($pattern);
        }

        return $array;
    }
}
