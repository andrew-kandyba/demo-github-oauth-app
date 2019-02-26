<?php declare(strict_types=1);

namespace App\Service;

use Slim\Collection;

/**
 * Simple github api client.
 */
class GithubApi
{
    /**
     * Github oauth app config.
     *
     * @var Collection
     */
    private $githubAppConfig;

    /**
     * Github user access token.
     *
     * @var string
     */
    private $githubUserAccessToken;

    /**
     * GithubApi constructor.
     *
     * @param Collection $githubAppConfig Github oauth app config data.
     */
    public function __construct(Collection $githubAppConfig)
    {
        $this->githubAppConfig = $githubAppConfig;
    }

    /**
     * Get auth url.
     *
     * @return array
     * @throws \Exception
     */
    public function getAuthorizeUrl(): array
    {
        $rootUrl = 'https://github.com/login/oauth/authorize';
        $queryParam = http_build_query(
            [
                'client_id' => $this->githubAppConfig->get('clientId'),
                'scope'     => $this->githubAppConfig->get('scope'),
                'state'     => bin2hex(random_bytes(16))
            ]
        );

        return ['auth_url' => $rootUrl . '?' . $queryParam];
    }

    /**
     * @param $code
     * @return array
     */
    public function receiveAccessTokenByCode(string $code): array
    {
        $rootUrl = 'https://github.com/login/oauth/access_token';

        $postData = http_build_query([
            'code'          => $code,
            'client_id'     => $this->githubAppConfig->get('clientId'),
            'client_secret' => $this->githubAppConfig->get('clientSecret')
        ]);

        $opts = [
            'http' =>
                [
                    'method'  => 'POST',
                    'content' => $postData,
                    'header'  => [
                        'Accept: application/json',
                        'Content-Type: application/x-www-form-urlencoded',
                        'Content-Length: ' . strlen($postData)
                    ],
                ]
        ];

        $context = stream_context_create($opts);

        $oauthCallbackUrlResponseData = json_decode(
            file_get_contents($rootUrl, false, $context),
            true
        );

        if (array_key_exists('access_token', $oauthCallbackUrlResponseData)) {
            $this->githubUserAccessToken = $oauthCallbackUrlResponseData['access_token'];
        }

        return $oauthCallbackUrlResponseData;
    }

    public function getAccessToken(): ?string
    {
        return $this->githubUserAccessToken;
    }

    /**
     *
     */
    public function getUserRepositoriesList(): array
    {
        $result = [];
        $apiEndpoint = 'https://api.github.com/user/repos';
        $contextOptions =  [
            'http' =>
                [
                    'method'  => 'GET',
                    'header'  => [
                        'User-Agent: PHP',
                        'Accept: application/json',
                        'Authorization: token ' . $this->githubUserAccessToken
                    ]
                ]
        ];

        $context = stream_context_create($contextOptions);
        $reposList = file_get_contents($apiEndpoint, false, $context);

        if (false !== $reposList) {
            foreach (json_decode($reposList, true) as $repoData)
            {
                $result[] = [
                    'full_name' => $repoData['full_name'],
                    'private' => $repoData['private'],
                ];
            }
        }

        return $result;
    }
}
