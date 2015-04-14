<?php

error_reporting(E_ALL);

try {
    /**
     * Read the configuration
     */
    $config = include(__DIR__."/../app/config/config.php");
    $loader = new \Phalcon\Loader();
    /**
     * We're a registering a set of directories taken from the configuration file
     */
    $loader->registerDirs(
        array(
            $config->application->controllersDir,
            $config->application->modelsDir
        )
    )->register();
    /**
     * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
     */
    $di = new \Phalcon\DI\FactoryDefault();
    /**
     * Include the application routes
     */
    $di->set('router', function(){
        return include(__DIR__."/../app/config/routes.php");
    });
    /**
     * The URL component is used to generate all kind of urls in the application
     */
    $di->set('url', function() use ($config) {
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri($config->application->baseUri);
        return $url;
    });

    //Register Volt as a service
    $di->set('voltService', function($view, $di) {

        $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);

        $volt->setOptions(array(
            "compiledPath" => "../app/compiled-templates/",
            "compiledExtension" => ".compiled"
        ));

        $compiler = $volt->getCompiler();

        //This binds the function name 'shuffle' in Volt to the PHP function 'str_shuffle'
        $compiler->addFunction('strtotime', 'strtotime');
        $compiler->addFunction('substr', 'substr');

        return $volt;
    });

    //Registering Volt as template engine
    $di->set('view', function() use ($config) {

        $view = new \Phalcon\Mvc\View();

        $view->setViewsDir($config->application->viewsDir);

        $view->registerEngines(array(
            //".volt" => 'Phalcon\Mvc\View\Engine\Volt'
            ".volt" => 'voltService'

        ));

        return $view;
    });
    /**
     * Database connection is created based in the parameters defined in the configuration file
     */
    $di->set('db', function() use ($config) {
        return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
            "host" => $config->database->host,
            "username" => $config->database->username,
            "password" => $config->database->password,
            "dbname" => $config->database->dbname,
            "charset"=> $config->database->charset
        ));
    });
    /**
     * Register the flash service with custom CSS classes
     */
    $di->set('flash', function(){
        return new Phalcon\Flash\Direct(array(
            'error' => 'alert alert-error',
            'success' => 'alert alert-success',
            'notice' => 'alert alert-info',
        ));
    });
    /**
     * Start the session the first time some component request the session service
     */
    $di->set('session', function() {
        $session = new \Phalcon\Session\Adapter\Files();
        $session->start();
        return $session;
    });

    $di->set('dispatcher', function() {

        $eventsManager = new \Phalcon\Events\Manager();

        $eventsManager->attach("dispatch:beforeException", function($event, $dispatcher, $exception) {

            //Handle 404 exceptions
            if ($exception instanceof \Phalcon\Mvc\Dispatcher\Exception) {
                $dispatcher->forward(array(
                    'controller' => 'error',
                    'action' => 'show404'
                ));
                return false;
            }

            //Handle other exceptions
            $dispatcher->forward(array(
                'controller' => 'error',
                'action' => 'show503'
            ));

            return false;
        });

        $dispatcher = new \Phalcon\Mvc\Dispatcher();

        //Bind the EventsManager to the dispatcher
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;

    }, true);

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application();
    $application->setDI($di);
    echo $application->handle()->getContent();
} catch (Phalcon\Exception $e) {
    echo $e->getMessage();
} catch (PDOException $e){
    echo $e->getMessage();
}