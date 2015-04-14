<?php

$router = new Phalcon\Mvc\Router();
$router->add('/', array(
    'controller' => 'blog',
    'action' => 'index',
));
$router->add('/blogitem/{id:[0-9]+}', array(
    'controller' => 'blog',
    'action' => 'show',
    'id' => 1,
));

$router->add('/blogitem/{id:[0-9]+}/success', array(
    'controller' => 'blog',
    'action' => 'show',
    'id' => 1,
));

//$router->addPost("/blog/save", "Blogcomments::save");
return $router;