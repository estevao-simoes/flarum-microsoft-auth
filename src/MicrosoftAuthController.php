<?php

namespace Flarum\Auth\Microsoft;

use Flarum\Forum\Auth\Registration;
use Flarum\Forum\Auth\ResponseFactory;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

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
    
    public function handle(Request $request) :ResponseInterface
    {        

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $this->settings->get('flarum-microsoft-auth.client_id'),    // The client ID assigned to you by the provider
            'clientSecret'            => $this->settings->get('flarum-microsoft-auth.client_secret'),   // The client password assigned to you by the provider
            'redirectUri'             => $this->url->to('forum')->route('auth.microsoft'),
            'urlAuthorize'            => 'https://login.microsoftonline.com/c2c7d69b-25e1-4dd3-bb7c-cec85a3e1913/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/c2c7d69b-25e1-4dd3-bb7c-cec85a3e1913/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me'
        ]);

        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {

            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            $authorizationUrl = $provider->getAuthorizationUrl() . '&scope=User.Read';

            // Get the state generated for you and store it to the session.
            $_SESSION['oauth2state'] = $provider->getState();

            // Redirect the user to the authorization URL.
            return new RedirectResponse($authorizationUrl);

            // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }

            exit('Invalid state');
        } else {

            try {

                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                // Using the access token, we may look up details about the
                // resource owner.
                $user = $provider->getResourceOwner($accessToken)->toArray();
                
                return $this->response->make(
                    'microsoft',
                    $user['id'],
                    function (Registration $registration) use ($user) {
                        $registration
                            ->provideTrustedEmail($user['mail'])
                            // ->provideAvatar($user[''])
                            ->suggestUsername($user['displayName'])
                            ->setPayload($user);
                    }
                );

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

                // Failed to get the access token or user details.
                exit($e->getMessage());
            }
        }
    }
}
