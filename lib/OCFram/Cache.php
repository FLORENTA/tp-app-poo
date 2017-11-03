<?php

namespace OCFram;

define("DS", DIRECTORY_SEPARATOR);

class Cache
{
    protected $uri;
    protected $app;
    protected $module;
    protected $action;
    protected $datasFolder;
    protected $viewsFolder;
    protected $datasFilename;
    protected $viewFilename;

    public function __construct($app, $module, $action, $uri)
    {
        $this->app = $app;
        $this->module = $module;
        $this->action = $action;
        $this->uri = $uri;
        $this->datasFolder = __DIR__ . DS . ".." . DS . ".." . DS . "tmp" . DS . "cache" . DS . "datas";
        $this->viewsFolder = __DIR__ . DS . ".." . DS . ".." . DS . "tmp" . DS . "cache" . DS . "views";
        $this->datasFilename = $this->datasFolder . DS . $this->app . DS . $this->module . DS . $this->uri . ".txt";
        $this->viewFilename = $this->viewsFolder . DS . $this->app . DS . $this->module . DS . $this->action . ".txt";

        if(!is_dir($this->datasFolder . DS . $this->app))
        {
            mkdir($this->datasFolder . DS . $this->app);
        }

        if(!is_dir($this->viewsFolder . DS . $this->app))
        {
            mkdir($this->viewsFolder . DS . $this->app);
        }

        if(!is_dir($this->datasFolder . DS . $this->app . DS . $this->module))
        {
            mkdir($this->datasFolder . DS . $this->app . DS . $this->module);
        }

        if(!is_dir($this->viewsFolder . DS . $this->app . DS . $this->module))
        {
            mkdir($this->viewsFolder . DS . $this->app . DS . $this->module);
        }
    }

    public function isInCache()
    {
        if(is_file($this->datasFilename) && is_file($this->viewFilename))
        {
            return true;
        }

        /* Else */
        return false;
    }

    public function createCache($datas, $view)
    {
        file_put_contents($this->datasFilename, time() . "\r\n" . serialize($datas));
        file_put_contents($this->viewFilename, time() . "\r\n" . $view);
    }

    public function generateViewFromCache($page)
    {
        /* On récupère le fichier de données en cache */
        $datas = file($this->datasFilename);
        /* Retrait du timestamp */
        unset($datas[0]);
        /* Désérialisation des données */
        $datas = unserialize($datas[1]);

        /* On récupère le fichier de la vue en cache (correspondant à l'uri) */
        $view = file_get_contents($this->viewFilename);
        var_dump($view);die;
        unset($view[0]);

        $page->addVar('title', $datas["title"]);
        $page->addVar('news', $datas["news"]);
        $page->addVar('comments', $datas["comments"]);

        $page->setPage($view[1]);

        return $page;
    }
}