<?php declare(strict_types=1);

use Slim\App;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Http\Environment;
use App\Service\GithubApi;
use App\Controller\FrontController;
use Psr\Container\ContainerInterface;

require __DIR__ . '/vendor/autoload.php';

$config = [
    'settings' => [
        'scope'        => implode(' ', ['repo, user']),
        'clientId'     => 'c67d56f9f041d114def5',
        'clientSecret' => 'bd0686193fa2c736995b967908d9bee10077abd0',
    ],
];

$app = new App($config);

/**
 * DI container.
 *
 * @var ContainerInterface
 */
$container = $app->getContainer();

/* Register services */
$container['view'] = function (ContainerInterface $container) {
    $view = new Twig('view');
    $router = $container->get('router');
    $uri = Uri::createFromEnvironment(new Environment($_SERVER));
    $view->addExtension(new Slim\Views\TwigExtension($router, $uri));

    return $view;
};

$container[GithubApi::class] = function (ContainerInterface $container) {
    return new GithubApi($container->get('settings'));
};

/* Register controllers */
$container[FrontController::class] = function (ContainerInterface $container) {
    return new FrontController($container->get('view'), $container->get(GithubApi::class));
};



/* Register routes */
$app->get('/', FrontController::class . ':login');
$app->get('/authorize', FrontController::class . ':authorize');

$app->run();
