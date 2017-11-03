<?php
namespace OCFram;

define("S", DIRECTORY_SEPARATOR);

abstract class BackController extends ApplicationComponent
{
  protected $action = '';
  protected $module = '';
  protected $page = null;
  protected $view = '';
  protected $managers = null;

  const DATASFOLDER = __DIR__ . S . ".." . S . ".." . S . "tmp" . S . "cache" . S . "datas" . S;
  const VIEWSFOLDER = __DIR__ . S . ".." . S . ".." . S . "tmp" . S . "cache" . S . "views" . S;

  public function __construct(Application $app, $module, $action)
  {
    parent::__construct($app);

    $this->managers = new Managers('PDO', PDOFactory::getMysqlConnexion());
    $this->page = new Page($app);

    $this->setModule($module);
    $this->setAction($action);
    $this->setView($action);
  }

  public function getModule()
  {
      return $this->module;
  }

  public function getView()
  {
      return $this->view;
  }

  public function execute()
  {
    $method = 'execute'.ucfirst($this->action);

    if (!is_callable([$this, $method]))
    {
      throw new \RuntimeException('L\'action "'.$this->action.'" n\'est pas définie sur ce module');
    }

    $this->$method($this->app->httpRequest());
  }

  public function page()
  {
    return $this->page;
  }

  public function setModule($module)
  {
    if (!is_string($module) || empty($module))
    {
      throw new \InvalidArgumentException('Le module doit être une chaine de caractères valide');
    }

    $this->module = $module;
  }

  public function setAction($action)
  {
    if (!is_string($action) || empty($action))
    {
      throw new \InvalidArgumentException('L\'action doit être une chaine de caractères valide');
    }

    $this->action = $action;
  }

  public function setView($view)
  {
    if (!is_string($view) || empty($view))
    {
      throw new \InvalidArgumentException('La vue doit être une chaine de caractères valide');
    }

    $this->view = $view;

    $this->page->setContentFile(__DIR__.'/../../App/'.$this->app->name().'/Modules/'.$this->module.'/Views/'.$this->view.'.php', false);
  }

    public function removeFromCache($news = null, $comment)
    {
        /* Si c'est un commentaire qui est édité ou supprimé, alors on ne supprime pas les
           éléments en cache de la partie admin... sinon, on supprime
        */
        if(false === $comment) {

            if (is_file(self::DATASFOLDER . $this->app->name() . $this->getModule() . "admin.txt")) {
                unlink(self::DATASFOLDER . $this->app->name() . $this->getModule() . "admin.txt");
            }

            if (is_file(self::VIEWSFOLDER . $this->app->name() . $this->getModule() . "admin.txt")) {
                unlink(self::VIEWSFOLDER . $this->app->name() . $this->getModule() . "admin.txt");
            }
        }

        /* Si on a ajouté un nouvel article, alors on supprime également la page d'accueil de la partie utilisateur */
        /* $news peut valoir "News" (dans le cas d'un ajout) ou l'id d'une news si "Modification/Suppression d'une news */
        if(!is_null($news))
        {
            /* Si c'est un commentaire qui est édité ou supprimé, alors on ne supprime pas les
               éléments en cache de la partie admin... sinon, on supprime
            */
            if(false === $comment)
            {
                if (is_file(self::DATASFOLDER . "FrontendNews.txt"))
                {
                    unlink(self::DATASFOLDER . "FrontendNews.txt");
                }

                if (is_file(self::VIEWSFOLDER . "FrontendNews.txt"))
                {
                    unlink(self::VIEWSFOLDER . "FrontendNews.txt");
                }
            }

            /* Si $news vaut un id de news, alors on supprime la vue et les données en cache correspondant à celle-ci */
            if(is_numeric($news))
            {
                if(is_file(self::DATASFOLDER . "FrontendNewsnews-".$news.".txt"))
                {
                    unlink(self::DATASFOLDER . "FrontendNewsnews-".$news.".txt");
                }

                if(is_file(self::VIEWSFOLDER . "FrontendNewsnews-".$news.".txt"))
                {
                    unlink(self::VIEWSFOLDER . "FrontendNewsnews-".$news.".txt");
                }
            }
        }
    }
}