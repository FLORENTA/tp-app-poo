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
        $this->datasFilename = $this->datasFolder . DS . $this->app . $this->module . $this->uri . ".txt";
        $this->viewFilename = $this->viewsFolder . DS . $this->app . $this->module . $this->uri . ".txt";
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
        $expirationDate = time() + 10;

        /* On met les infos dans le fichier, avec une date d'expiration de 10 secondes (pour le test) */
        file_put_contents($this->datasFilename, $expirationDate . "\r\n" . serialize($datas));
        file_put_contents($this->viewFilename, $expirationDate . "\r\n" . $view);
    }

    public function generateViewFromCache($page)
    {
        /* Date actuelle */
        $currentTime = time();

        /* On récupère le fichier de données en cache */
        $datas = file($this->datasFilename);

        /* Récupération de la date d'expiration */
        $datasExpirationDate = $datas[0];

        /* Si date d'expiration du fichier de données dépassée */
        /* On supprime le fichier de données et la vue */
        /* Etant donnée que le fichier de données et la vue en cache ont
           la même date d'expiration, une seule vérification par rapport
           au fichier de données suffit ...
        */
        if($currentTime > $datasExpirationDate)
        {
            /* Suppression du fichier de données */
            unlink($this->datasFilename);

            /* Suppression de la vue */
            unlink($this->viewFilename);

            /* On redirige */
            header("location: .");
        }

        /* Sinon, le fichier n'a pas expiré */
        /* Retrait du timestamp du tableau */
        unset($datas[0]);

        /* Désérialisation des données */
        $datas = unserialize($datas[1]);

        /* On récupère la vue en cache (correspondant à l'uri...) */
        $view = file_get_contents($this->viewFilename);

        /* Spécification de Frontend car même méthode dans le backend ... */
        if($this->app === "Frontend" && $this->action === "index")
        {
            $page->addVar("title", $datas["title"]);
            $page->addVar("listeNews", $datas["listeNews"]);
        }

        if($this->app === "Frontend" && $this->action === "show")
        {
            $page->addVar('title', $datas["title"]);
            $page->addVar('news', $datas["news"]);
            $page->addVar('comments', $datas["comments"]);
        }

        if($this->app = "Frontend" && $this->action === "insertComment")
        {
            $page->addVar('comment', $datas["comment"]);
            $page->addVar('form', $datas["form"]);
            $page->addVar('title', $datas["title"]);
        }

        /* Substr to remove the timestamp from the txt file */
        $page->setPage(substr($view, 10));

        return $page;
    }
}