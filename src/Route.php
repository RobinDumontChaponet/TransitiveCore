<?php

namespace Transitive\Core;

class Route
{
    public function __construct($presenter, $view = null, $user = null, $auth = null)
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
     * @var void
     */
    public $presenter;

    /**
     * @var void
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
     * @return Presenter
     */
    public function getPresenter()
    {
        return $this->presenter;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }
}
