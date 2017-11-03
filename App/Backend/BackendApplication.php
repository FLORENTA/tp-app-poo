<?php
namespace App\Backend;

use \OCFram\Application;
use OCFram\Cache;

class BackendApplication extends Application
{
  public function __construct()
  {
    parent::__construct();

    $this->name = 'Backend';
  }

    public function run()
    {
        if ($this->user->isAuthenticated()) {

            $controller = $this->getController();
            $uri = $this->httpRequest()->requestURI();
            $uri = preg_replace("/\/|.html/", "", $uri);

            $cache = new Cache($this->name, $controller->getModule(), $controller->getView(), $uri);

            /* If not in cache (datas && view) */
            if (false === $cache->isInCache()) {

                $controller->execute();

                if ($uri === "admin") {
                    $datas = $controller->page()->getVars();
                    $view = $controller->page()->getContent();

                    $cache->createCache($datas, $view);
                }

                $this->httpResponse->setPage($controller->page());
                $this->httpResponse->send();
            } else {

                $view = $cache->generateViewFromCache($controller->page());
                $this->httpResponse->setPage($view);
                $this->httpResponse->sendCachedView();
            }
        }
        else
        {
            $controller = new Modules\Connexion\ConnexionController($this, 'Connexion', 'index');

            $controller->execute();

            $this->httpResponse->setPage($controller->page());
            $this->httpResponse->send();
        }
    }
}