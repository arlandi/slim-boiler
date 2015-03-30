<?php

namespace SlimBoiler;

class View extends \Slim\View
{
    /**
    * @var  String layout file
    */
    public $layout = false;

    /**
    * @var Array Global array
    */
    public $global = array();

    /**
    * Set layout file
    * @var String layout file
    */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
    * Append values to existing global values
    *@var array data
    */
    public function appendGlobalData(Array $data)
    {
        $this->global = array_merge($this->global,$data);
    }

    /**
     * Display template
     *
     * This method overrides parent display method which echoes output
     *
     * @param  string   $template   Pathname of template file relative to templates directory
     * @param  array    $data       Any additonal data to be passed to the template.
     */
    public function display($template, $data = null)
    {
        echo $this->fetch($template, $data);
    }

    /**
    * Render template
    * @var string $template Template to be rendered
    */
    public function render($template = '', $data = null)
    {
        $template = is_string($template) ? $template . '.php' : null;
        if($template){
            $this->appendData(array('global' => $this->global));
            $content =  parent::render($template);
        }
        else{
            $content = '';
        }
        extract(array('content' => $content, 'global' => $this->global));
        if($this->layout){
            $layoutPath = $this->getTemplatesDirectory() . DIRECTORY_SEPARATOR . ltrim($this->layout, '/');
            if ( !is_readable($layoutPath) ) {
                throw new \RuntimeException('View cannot render layout `' . $layoutPath );
            }
            ob_clean();
            ob_start();
            require $layoutPath;
            return ob_get_clean();
        }
        return $content;
    }

}
