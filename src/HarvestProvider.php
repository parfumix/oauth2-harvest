<?php

namespace Harvest;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class HarvestAuthorizationException extends \Exception {}

class HarvestProvider extends AbstractProvider {

    protected $apiUrl = 'https://api.harvestapp.com';

    protected $appDetails = [];

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl() {
        return 'https://id.getharvest.com/oauth2/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params) {
        return 'https://id.getharvest.com/api/v1/oauth2/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token) {
        return $this->apiUrl . '/v2/users/me';
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes() {
        return ['harvest:all'];
    }

    /**
     * Checks a provider response for errors.
     *
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @throws HarvestAuthorizationException
     */
    protected function checkResponse(ResponseInterface $response, $data) {
        if ($response->getStatusCode() >= 400) {
            throw new HarvestAuthorizationException(
                $response->getReasonPhrase(), $response->getStatusCode()
            );
        } elseif (isset($data['error'])) {
            throw new HarvestAuthorizationException(
                $data['error'] ?? $response->getReasonPhrase(), $response->getStatusCode()
            );
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param  array $response
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token) {
        return new HarvestResourceOwner($response);
    }

    /**
     * Set up additional headers .
     *
     * @return array
     */
    public function getDefaultHeaders() {
        return array(
            'Accept' => 'application/json',
            'User-Agent' => sprintf('%s (%s)', $this->appDetails['name'] ?? 'MyApp', $this->appDetails['email'] ?? 'example@mail.com')
        );
    }

}