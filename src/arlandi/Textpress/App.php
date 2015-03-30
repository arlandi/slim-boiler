<?php

namespace SlimBoiler;

class App
{

    /**
    * Configuration
    *
    * @var array
    */
    public $config = array();

    /**
    * Array of file names
    *
    * @var array
    */
    public $fileNames = array();

    /**
    * Articles
    *
    * @var array
    */
    public $allArticles = array();

    /**
    * View data
    *
    * @var array
    */
    public $viewData = array();

    /**
    * Enable or disable layout
    *
    * @var bool
    */
    public $enableLayout = true;

    /**
    * Slim object
    *
    * @var Slim
    */
    public $slim;

    /**
    * Array of all categories in the blog
    *
    * @var array
    */
    public $categories = array();

    /**
    * Array of all tags in the blog
    * A tag is an object of class Tag with name and count attributes
    *
    * @var array
    */
    public $tags = array();

    /**
    * Base directory of current active theme
    *
    * @var string
    */
    public $themeBase;

    /**
    * Constructor
    *
    * @param Slim $slim Object of slim
    */
    public function __construct(\Slim\Slim $slim, $config)
    {
        $this->slim = $slim;
        $this->setConfig($config);
        if (isset($config['cache']) && $config['cache']['enabled']) {
            $this->slim->add(new Cache($config['cache']));
        }
    }

    /**
    * Set configurations
    *
    * @param array $config Configuration array
    */
    public function setConfig($config)
    {
        $this->config = $config;
        $this->slim->config($config);
    }

    /**
    * Getter function to get config variable
    *
    * @var string $configVar Config variable
    * @return Configuration value
    */
    public function getConfig($configVar)
    {
        return isset($this->config[$configVar])
                ? $this->config[$configVar]
                : null ;
    }

    /**
    * Initialize
    */
    public function init()
    {
        $this->themeBase = './templates';
        $this->slim->view()->setTemplatesDirectory($this->themeBase);
        $this->setViewConfig();
        $this->setRoutes();
    }

    /**
    * @return array Article file names
    */
    public function getfileNames()
    {
        if (empty($this->fileNames))
        {
            $iterator = new \DirectoryIterator($this->getConfig('article.path'));
            $files = new \RegexIterator($iterator,'/\\'.$this->getConfig('file.extension').'$/');
            foreach($files as $file){
                if($file->isFile()){
                    $this->fileNames[] = $file->getFilename();
                }
            }
            rsort($this->fileNames);
        }
        return $this->fileNames;
    }

   /**
    * Warpper function to get host URL.
    * From site.baseurl or auto detected by Slim.
    *
    * @return Host URL string
    */
    public function getUrl()
    {
        return $this->getConfig('site.baseurl')
                ? $this->getConfig('site.baseurl')
                : $this->slim->request()->getUrl();
    }

    /**
    * Sort articles based on date
    *
    * @param array $articles Array of articles
    */
    public function sortArticles($articles)
    {
        $results    = array();
        foreach($articles as $article) {
            $date = new \DateTime($article->getDate());
            $timestamp = $date->getTimestamp();
            $timestamp = array_key_exists($timestamp, $results) ? $timestamp + 1 : $timestamp;
            $results[$timestamp] = $article;
        }
        krsort($results);
        return $results;
    }

    /**
    * Filter list of articles based on the meta key-value
    * Mainly used in categories and tags, but you can extend for other custom
    * meta keys also. Just add the routes and update routing function to include those routes
    *
    * @param String $filter meta key to be searched in articles
    * @param string $value value to be mached with
    * @return array list of article matching the criteria
    */
    public function filterArticles($filter,$value){
        $articles = array();
        foreach ($this->allArticles as $path => $article) {
            if ( $article->getMeta($filter)
                && array_key_exists($value, $article->getMeta($filter)))
                $articles[$path] = $article;
        }
        return $this->viewData['articles'] = $articles;
    }

    /**
    * Sets view data for an article route.
    *
    * @param string $url URL without prefix
    */
    public function setArticle($path)
    {
        if (!isset( $this->allArticles[$path] )) {
            $this->notFound();
        }
        $article = $this->allArticles[$path];
        $this->slim->view()->appendGlobalData($article->getMeta());
        return $this->viewData['article'] = $article;
    }

    /**
    * Load archives based on current route
    *
    * @param array $route Route params
    */
    public function loadArchives($route)
    {
        switch (count($route)) {
            case 0 :
                $this->setArchives();
                break;
            case 1 :
                $this->setArchives(implode('-',$route),'Y');
                break;
            case 2 :
                $this->setArchives(implode('-',$route),'Y-m');
                break;
            case 3 :
                $this->setArchives(implode('-',$route),'Y-m-d');
                break;
        }
        return $this->viewData['archives'];
    }

    /**
    * Sets archives to be shown to viewData array.
    *
    * @param  Date $date from arguments passed via rout
    * @param  String $format Date format
    * @return array archives
    */
    public function setArchives($date=null,$format='')
    {
        $this->viewData['archives']  = array();
        $archives = array();
        if (is_null($date)) {
            $archives = $this->allArticles;
        }
        else {
            foreach($this->allArticles as $article){
                if ($date == $article->getDate($format))
                    $archives[] = $article;
            }
        }
        return $this->viewData['archives'] = $archives;
    }

    /**
    * Sets view data for sitemap.
    *
    * @return array sitemap url set.
    */
    public function setSitemapData()
    {
        $sitemapData = array();
        foreach ($this->allArticles as  $article) {
            $sitemapData[] = array(
                    'loc' => $article->getUrl(),
                    'lastmod' => $article->getDate(),
                    'changefreq' => 'daily',
                    'priority' => '0.9'
                );
        }
        $this->viewData['baseUrl'] = $this->getUrl();
        return $this->viewData['sitemapData'] = $sitemapData;
    }

    /**
    * Custom 404 handler
    * Function can be called for handling 404 errors
    */
    public function notFound()
    {
        $this->slim->notFound();
    }

    /**
    * Set Application routes based on the routes specified in config
    * Also sets layout file if it's enabled for that specific route
    */
    public function setRoutes()
    {
        $this->_routes = $this->getConfig('routes');
        $self = $this;
        $prefix = $this->getConfig('prefix');
        foreach ($this->_routes as $key => $value) {
            $this->slim->map($prefix . $value['route'], function() use($self, $key, $value){
                $args = func_get_args();
                $layout = isset($value['layout']) ? $value['layout'] : true;

                // This will store a custom function if defined into the route
                $custom = isset($value['custom']) ? $value['custom'] : false;

                $self->slim->view()->appendGlobalData(array("route" => $key));
                $template = isset($value['template']) ? $value['template'] : false;

                //set view data for article  and archives routes
                switch ($key) {
                    case '__root__' :
                    case 'rss'      :
                    case 'atom'     :
                        $self->allArticles = array_slice($self->allArticles, 0, 10);
                        break;
                    case 'sitemap'  :
                        $self->slim->response->headers->set('Content-Type', 'text/xml');
                        $self->setSitemapData();
                        break;
                    case 'article'  :
                        $article = $self->setArticle($self->getPath($args));
                        $template = ($article->getMeta('template') && $article->getMeta('template') !="")
                                        ? $article->getMeta('template')
                                        : $template;
                        break;
                    case 'archives' :
                        $self->loadArchives($args);
                        break;
                    case 'category' :
                    case 'tag'      :
                        $self->filterArticles($key,$args[0]);
                        break;

                    // If key is not matched, check if a custom function is declared
                    default:
                        if ($custom && is_callable($custom))
                            call_user_func($custom, $self, $key, $value);
                        break;
                }
                if(!$layout){
                    $self->enableLayout = false;
                }
                else{
                    $self->setLayout($layout);
                }
                // render the template file
                $self->render($template);

            })->via('GET')
              ->name($key)
              ->conditions(
                isset($value['conditions']) ? $value['conditions']: array()
            );
        }

        // Register not found handler
        $this->slim->notFound(function () use ($self) {
            $self->slim->render('404');
        });
    }

    /**
    * Function to get full path of article file from its filename
    *
    * @param $path String File name
    * @return String Path to file or false if file does not exists
    */
    public function getArticlePath($path)
    {
        if(in_array($path , $this->getFileNames())){
            return $this->getConfig('article.path') . '/' . $path ;
        }
        return false;
    }

    /**
    * Constructs file name from route params
    *
    * @param $params Array route parameters
    * @return String file name
    */
    public function getPath($params)
    {
        $slug = array_pop($params);
        $date = implode('-', $params);
        return $this->getArticleUrl($date,$slug);
    }

    /**
    * Creates url from a Date and Title
    *
    * @param string $date Date of article
    * @param string $slug Article title
    */
    public function getArticleUrl($date,$slug)
    {
        $date = new \DateTime($date);
        $date = $date->format('Y-m-d');
        $dateSplit = explode('-', $date);
        return $this->slim->urlFor(
                                'article',
                                array(
                                    'year'=>$dateSplit[0],
                                    'month'=>$dateSplit[1],
                                    'date' => $dateSplit[2],
                                    'article'=>$slug
                                )
                            ) ;
    }

    /**
    * Slugize an article title
    * @param string  $string  article title
    * @return string URL slug corresponding to the string
    */
    public function slugize($str)
    {
        $str = strtolower(trim($str));

        $chars = array("ä", "ö", "ü", "ß");
        $replacements = array("ae", "oe", "ue", "ss");
        $str = str_replace($chars, $replacements, $str);

        $pattern = array("/(é|è|ë|ê)/", "/(ó|ò|ö|ô)/", "/(ú|ù|ü|û)/");
        $replacements = array("e", "o", "u");
        $str = preg_replace($pattern, $replacements, $str);

        $pattern = array(":", "!", "?", ".", "/", "'");
        $str = str_replace($pattern, "", $str);

        $pattern = array("/[^a-z0-9-]/", "/-+/");
        $str = preg_replace($pattern, "-", $str);

        return $str;
    }

    /**
    * Set config values to View
    * @todo make it comfortable
    */
    public function setViewConfig()
    {
        $themeDir   = ltrim($this->themeBase, "./");
        $themeBase  = "/" . $themeDir;
        $data = array(
                'date.format' => $this->getConfig('date.format'),
                'author.name' => $this->getConfig('author.name'),
                'site.name' => $this->getConfig('site.name'),
                'site.title' => $this->getConfig('site.title'),
                'site.description' => $this->getConfig('site.description'),
                'disqus.username' => $this->getConfig('disqus.username'),
                'assets.prefix' => $this->getConfig('assets.prefix'),
                'google.analytics' => $this->getConfig('google.analytics'),
                'prefix' => $this->getConfig('prefix'),
                'base.url' => $this->getUrl()
            );
        $this->slim->view()->appendGlobalData($data);
    }

    /**
    * Collects categories from all articles
    *
    * @param string $meta Article meta data
    * @return array of distinct categories
    */
    public function collectCategories($meta)
    {
        $temp = array();
        if(array_key_exists('category', $meta) && $meta['category']){
            $categories = explode(',', trim($meta['category'], ', '));
            foreach ($categories as  $category) {
                $slug = $this->slugize($category);
                $temp[$slug] = trim($category);
            }
            $this->categories = array_merge($this->categories,$temp);
        }
        return $temp;
    }

    /**
    * Collect tags from all articles to build tag cloud
    * Each tag will be an object of Tag with name and count
    * Use $tag->name and $tag->count to get the name and number of occurances of each tag
    *
    * @param string $meta Article meta data
    * @return collection of Tag objects
    */
    public function collectTags($meta)
    {
        $temp = array();
        if(array_key_exists('tag', $meta) && $meta['tag']){
            $tags = explode(',', trim($meta['tag'], ', '));
            foreach ($tags as $tag) {
                $slug = $this->slugize($tag);
                if(isset($this->tags[$slug])) {
                    $temp[$slug] = $this->tags[$slug];
                    $temp[$slug]->count++;
                }
                else {
                    $temp[$slug] = new Tag(trim($tag));
                }
            }
            $this->tags = array_merge($this->tags,$temp);
        }
        return $temp;
    }

    /**
    * Helper function for date formatting
    *
    * @param $date Input date
    * @param $format Date format
    */
    public function dateFormat($date,$format=null)
    {
        $format = is_null($format) ? $this->getConfig('date.format') : $format;
        $date  = new \DateTime($date);
        return $date->format($format);
    }

    /**
    * @return array view data
    */
    public function getViewData($key = null)
    {
        return is_null($key)
                    ? $this->viewData
                    : ( isset($this->viewData[$key])
                        ? $this->viewData[$key]
                        : false );
    }

    /**
    * Set layout file
    */
    public function setLayout($layout)
    {
        $layoutFile = is_bool($layout) ? $this->slim->config('layout.file') . '.php'
                                       : $layout . ".php";
        $this->slim->view()->setLayout($layoutFile);
    }

    /**
    * Render template
    *
    * @param string $template template file to be rendered
    */
    public function render($template)
    {
        $this->slim->render($template,$this->getViewData());
    }

    /**
    * Run slim
    */
    public function run()
    {
        $this->init();
        $this->slim->run();
    }
}
