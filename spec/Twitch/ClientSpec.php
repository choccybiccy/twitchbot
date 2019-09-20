<?php

namespace spec\Choccybiccy\TwitchBot\Twitch;

use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ClientSpec extends ObjectBehavior
{
    public function let(ClientInterface $client)
    {
        $this->beConstructedWith(uniqid(), $client);
    }

    public function it_can_return_the_user(
        ClientInterface $client,
        ResponseInterface $response,
        StreamInterface $stream
    ) {
        $userData = [
            'id' => mt_rand(1000, 9999),
            'login' => 'someUser',
            'display_name' => 'theUser',
            'type' => '',
            'broadcaster_type' => 'affiliate',
            'profile_image_url' => 'http://some.image.url/image.jpg',
            'offline_image_url' => 'http://some.image.url/offline.jpg',
            'viewer_count' => mt_rand(1, 99999),
        ];
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn(json_encode(['data' => [$userData]]));
        $client->request('get', '/helix/users')->shouldBeCalled()->willReturn($response);
        $this->getUser()->shouldReturn($userData);
    }

    public function it_can_return_follower_count(
        ClientInterface $client,
        ResponseInterface $userResponse,
        StreamInterface $userStream,
        ResponseInterface $subscribersResponse,
        StreamInterface $subscribersStream
    ) {
        $userData = [
            'id' => mt_rand(1000, 9999),
            'login' => 'someUser',
            'display_name' => 'theUser',
            'type' => '',
            'broadcaster_type' => 'affiliate',
            'profile_image_url' => 'http://some.image.url/image.jpg',
            'offline_image_url' => 'http://some.image.url/offline.jpg',
            'view_count' => mt_rand(1, 99999),
        ];
        $userResponse->getBody()->willReturn($userStream);
        $userStream->getContents()->willReturn(json_encode(['data' => [$userData]]));
        $client->request('get', '/helix/users')->shouldBeCalled()->willReturn($userResponse);

        $subscriberCount = mt_rand(1000, 9999);
        $subscribersResponse->getBody()->willReturn($subscribersStream);
        $subscribersStream->getContents()->willReturn(json_encode([
            'total' => $subscriberCount,
            'data' => [
                [
                    'from_id' => 1234,
                    'to_id' => $userData['id'],
                ]
            ]
        ]));

        $client->request('get', '/helix/users/follows?to_id=' . $userData['id'])
            ->shouldBeCalled()
            ->willReturn($subscribersResponse);

        $this->getFollowerCount()->shouldReturn($subscriberCount);
    }

    public function it_can_return_stream(
        ClientInterface $client,
        ResponseInterface $userResponse,
        StreamInterface $userStream,
        ResponseInterface $response,
        StreamInterface $stream
    ) {
        $userData = [
            'id' => mt_rand(1000, 9999),
            'login' => 'someUser',
            'display_name' => 'theUser',
            'type' => '',
            'broadcaster_type' => 'affiliate',
            'profile_image_url' => 'http://some.image.url/image.jpg',
            'offline_image_url' => 'http://some.image.url/offline.jpg',
            'view_count' => mt_rand(1, 99999),
        ];
        $userResponse->getBody()->willReturn($userStream);
        $userStream->getContents()->willReturn(json_encode(['data' => [$userData]]));
        $client->request('get', '/helix/users')->shouldBeCalled()->willReturn($userResponse);

        $streamData = [
            'id' => mt_rand(10000, 99999),
            'type' => 'live',
            'viewer_count' => mt_rand(1000, 9999),
        ];
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn(json_encode(['data' => [$streamData]]));

        $client->request('get', '/helix/streams?user_id=' . $userData['id'])
            ->shouldBeCalled()
            ->willReturn($response);

        $this->getStream()->shouldReturn($streamData);
    }
}
