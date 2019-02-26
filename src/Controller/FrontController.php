<?php declare(strict_types=1);

namespace App\Controller;

use Slim\Views\Twig;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Service\GithubApi;
use Psr\Http\Message\ResponseInterface;

/**
 * App controller
 */
class FrontController
{
    /**
     * View engine.
     *
     * @var Twig
     */
    private $view;

    /**
     * Github api client.
     *
     * @var GithubApi
     */
    private $githubApiClient;

    public function __construct(Twig $view, GithubApi $githubApiClient)
    {
        $this->view = $view;
        $this->githubApiClient = $githubApiClient;
    }

    /**
     * Default route.
     *
     * @param Request  $request  Request object.
     * @param Response $response Response object.
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function login(Request $request, Response $response): ResponseInterface
    {
        return $this->view->render(
            $response,
            'login.html',
            $this->githubApiClient->getAuthorizeUrl()
        );
    }

    /**
     * Oauth callback route.
     *
     * @param Request  $request  Request object.
     * @param Response $response Response object.
     *
     * @return Response
     */
    public function authorize(Request $request, Response $response): ResponseInterface
    {
        $code = $request->getQueryParam('code');
        $this->githubApiClient->receiveAccessTokenByCode($code);

        return $this->view->render(
            $response,
            'success.html',
            [
                'access_token'   => $this->githubApiClient->getAccessToken(),
                'user_repo_list' => $this->githubApiClient->getUserRepositoriesList(),
            ]
        );
    }
}
