<?php
namespace OCFram;

class Page extends ApplicationComponent
{
  protected $contentFile;
  protected $vars = [];
  protected $content;

  public function addVar($var, $value)
  {
    if (!is_string($var) || is_numeric($var) || empty($var))
    {
      throw new \InvalidArgumentException('Le nom de la variable doit être une chaine de caractères non nulle');
    }

    $this->vars[$var] = $value;
  }

  public function getVars()
  {
      return $this->vars;
  }

  public function getContent()
  {
      /* Mise en cache de "$user"
         Si authentifié au moment de la mise en cache...
         l'interface de l'admin s'affichera pour les non authentifiés !...
      */
      $user = $this->app->user();

      extract($this->vars);

      ob_start();
        require $this->contentFile;
      return ob_get_clean();
  }

  public function getView()
  {
      $user = $this->app->user();

      $content = $this->content;

      ob_start();
        require __DIR__.'/../../App/'.$this->app->name().'/Templates/layout.php';
      return ob_get_clean();
  }

  public function getGeneratedPage()
  {
    if (!file_exists($this->contentFile))
    {
      throw new \RuntimeException('La vue spécifiée n\'existe pas');
    }

    $user = $this->app->user();

    extract($this->vars);

    ob_start();
      require $this->contentFile;
    $content = ob_get_clean();

    ob_start();
      require __DIR__.'/../../App/'.$this->app->name().'/Templates/layout.php';
    return ob_get_clean();
  }

  public function setContentFile($contentFile)
  {
    if (!is_string($contentFile) || empty($contentFile))
    {
        throw new \InvalidArgumentException('La vue spécifiée est invalide');
    }

    $this->contentFile = $contentFile;
  }

  public function setPage($view)
  {
      $this->content = $view;
  }
}