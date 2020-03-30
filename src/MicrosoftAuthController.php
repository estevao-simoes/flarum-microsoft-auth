<?php

namespace Flarum\Auth\Microsoft;

use Exception;
use Flarum\Forum\Auth\Registration;
use Flarum\Forum\Auth\ResponseFactory;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

class MicrosoftAuthController implements RequestHandlerInterface
{
    /**
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @param ResponseFactory $response
     * @param SettingsRepositoryInterface $settings
     * @param UrlGenerator $url
     */
    public function __construct(ResponseFactory $response, SettingsRepositoryInterface $settings, UrlGenerator $url)
    {
        $this->response = $response;
        $this->settings = $settings;
        $this->url = $url;
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws \Stevenmaguire\OAuth2\Client\Provider\Exception
     * @throws Exception
     */
    public function handle(Request $request): ResponseInterface
    {
        $redirectUri = $this->url->to('forum')->route('auth.microsoft');

        $provider = new Microsoft([
            // Required
            'clientId'                  => $this->settings->get('flarum-auth-microsoft.app_id'),
            'clientSecret'              => $this->settings->get('flarum-auth-microsoft.app_secret'),
            'redirectUri'               => $redirectUri
        ]);

        $session = $request->getAttribute('session');
        $queryParams = $request->getQueryParams();

        $code = array_get($queryParams, 'code');

        if (! $code) {
            $authUrl = $provider->getAuthorizationUrl();
            $session->put('oauth2state', $provider->getState());

            return new RedirectResponse($authUrl.'&display=popup');
        }

        $state = array_get($queryParams, 'state');

        if (! $state || $state !== $session->get('oauth2state')) {
            $session->remove('oauth2state');

            throw new Exception('Invalid state');
        }

        $token = $provider->getAccessToken('authorization_code', compact('code'));

        $user = $provider->getResourceOwner($token);

        return $this->response->make(
            'microsoft',
            $user->getId(),
            function (Registration $registration) use ($user) {
                $registration
                    ->provideTrustedEmail($user->getEmail())
                    ->provideAvatar($user->getPictureUrl())
                    ->suggestUsername($user->getName())
                    ->setPayload($user->toArray());
            }
        );
    }
}
