<?php

namespace Choccybiccy\TwitchBot\Twitch;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Client.
 */
class Client
{
    const API_URL = 'https://api.twitch.tv/helix';

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * Client constructor.
     *
     * @param ClientInterface $httpClient
     */
    public function __construct(string $bearerToken, ?ClientInterface $httpClient = null)
    {
        if (!$httpClient) {
            $httpClient = new \GuzzleHttp\Client([
                'base_uri' => self::API_URL,
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearerToken,
                ]
            ]);
        }
        $this->httpClient = $httpClient;
    }

    /**
     * @return array
     */
    public function getUser(): array
    {
        $response = $this->apiCall('users');
        return current($response['data']);
    }

    /**
     * @return int
     * @throws GuzzleException
     */
    public function getFollowerCount(): int
    {
        $id = $this->getUser()['id'];
        $response = $this->apiCall('users/follows?to_id=' . $id);
        return (int) $response['total'];
    }

    /**
     * @param int|null $userId
     *
     * @return array
     * @throws GuzzleException
     */
    public function getStream(int $userId = null): array
    {
        $id = $userId ?? $this->getUser()['id'];
        $response = $this->apiCall('streams?user_id=' . $id);
        return current($response['data']) ?: [];
    }

    /**
     * @return int
     */
    public function getSubscriberCount(): int
    {
    }

    /**
     * @param string $endpoint
     *
     * @return array
     * @throws GuzzleException
     */
    protected function apiCall(string $endpoint): array
    {
        $endpoint = ltrim($endpoint, 'l');
        $response = $this->httpClient->request('get', '/helix/' . $endpoint);
        return json_decode($response->getBody()->getContents(), true);
    }
}