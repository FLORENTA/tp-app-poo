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

    /* On récupère l'uri qui servira a donner à un nom au fichier de données */
    $uri = $this->httpRequest()->requestURI();
    /* Retrait du slash et de ".html" dans $uri */
    $uri = preg_replace("/\/|.html/", "", $uri);

    /* On instancie la classe cache avec quelques arguments (Nom application, module, action, uri) */
    $cache = new Cache($this->name, $controller->getModule(), $controller->getView(), $uri);

    /* On vérifie si les données et la vue correspondante à l'uri ne sont déjà pas en cache */
    /* Si faux, alors on exécute le contrôleur qui va récupérer les infos de la vue etc...
       Création d'une méthode getVars() dans la classe Page, chargée de retourner les données
       récupérée par le contrôleur exécuté
       Création d'une méthode getContent() dans la classe Page, chargée de retourner la vue et son
       contenu, mais pas le layout.
    */

    if(false === $cache->isInCache())
    {
        /* On exécute le contrôleur */
        $controller->execute();

        if($controller->getView() === "index" || $controller->getView() === "show") {
            /* Voir classe Page */
            $datas = $controller->page()->getVars();

            /* Voir classe Page */
            $view = $controller->page()->getContent();

            /* On met en cache les données et la vue générée après exécution du contrôleur */
            $cache->createCache($datas, $view);
        }

        /* On continue le processus initial ... */
        $this->httpResponse->setPage($controller->page());
        $this->httpResponse->send();
    }

    /* Sinon, si des données sont en cache (pour l'uri concernée) */
    else{
        /* On récupère la page completée avec les données désérialisées et la vue
           précédemment stockées en cache
        */
        $view = $cache->generateViewFromCache($controller->page());

        /* On set la page */
        $this->httpResponse->setPage($view);
        $this->httpResponse->sendCachedView();
    }
  }
}