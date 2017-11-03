<?php
namespace App\Frontend;

use \OCFram\Application;
use OCFram\Cache;

class FrontendApplication extends Application
{
  public function __construct()
  {
    parent::__construct();

    $this->name = 'Frontend';
  }

  public function run()
  {
    $controller = $this->getController();

    $uri = $this->httpRequest()->requestURI();
    $uri = preg_replace("/\/|.html/", "", $uri);

    $cache = new Cache($this->name, $controller->getModule(), $controller->getView(), $uri);

    /* If not in cache (datas && view) */
    if(false === $cache->isInCache())
    {
        $controller->execute();

        $datas = $controller->page()->getVars();
        $view = $controller->page()->getView();

        $cache->createCache($datas, $view);

        $this->httpResponse->setPage($controller->page());
        $this->httpResponse->send();
    }
    else{
        $view = $cache->generateViewFromCache($controller->page());

        $this->httpResponse->setPage($view);
    }
  }
}