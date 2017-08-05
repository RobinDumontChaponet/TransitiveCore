<?php

namespace Transitive\Core;

class Route
{
    public function __construct(Presenter $presenter, View $view = null, $user = null, $auth = null)
    {
        $this->presenter = $presenter;

        if(isset($view))
            $this->view = $view;
        elseif(is_string($this->presenter))
            $this->view = $this->presenter;

        $this->user = $user;
        $this->auth = $auth;
    }

    /**
     * @var Presenter | string
     */
    public $presenter;

    /**
     * @var View | string | null
     */
    public $view;

    /**
     * @var void
     */
    public $user;

    /**
     * @var void
     */
    public $auth;

    /**
     * @return Presenter | string
     */
    public function getPresenter()
    {
        return $this->presenter;
    }

    /**
     * @return View | string | null
     */
    public function getView()
    {
        return $this->view;
    }
}
