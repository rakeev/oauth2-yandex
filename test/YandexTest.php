<?php

namespace Aego\OAuth2\Client\Test\Provider;

use Aego\OAuth2\Client\Provider\Yandex;
use Aego\OAuth2\Client\Provider\YandexResourceOwner;

class YandexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Yandex
     */
    private $provider;

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();

        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertEquals('oauth.yandex.ru', $uri['host']);
        $this->assertEquals('/authorize', $uri['path']);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertEquals('code', $query['response_type']);

        $this->assertNotNull($this->provider->getState());
    }

    public function testGetBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);

        $this->assertEquals('https://oauth.yandex.ru/token', $url);
    }

    public function testGetAccessToken()
    {
        $response = $this->getMock('Psr\Http\Message\ResponseInterface');

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn('{"access_token":"mock_access_token","token_type":"bearer","expires_in":3600}');

        $response->expects($this->any())
            ->method('getHeader')
            ->willReturn(['content-type' => 'json']);

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);

        $client = $this->getMock('GuzzleHttp\ClientInterface');
        $this->provider->setHttpClient($client);

        $client->expects($this->once())
            ->method('send')
            ->willReturn($response);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertGreaterThan(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    /**
     * @expectedException \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @expectedExceptionCode 400
     * @expectedExceptionMessage bad_verification_code: Error message
     */
    public function testThrowExceptionWhenCouldNotGetAccessToken()
    {
        $response = $this->getMock('Psr\Http\Message\ResponseInterface');

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn('{"error_description":"Error message","error":"bad_verification_code"}');

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(400);

        $client = $this->getMock('GuzzleHttp\ClientInterface');
        $this->provider->setHttpClient($client);

        $client->expects($this->once())
            ->method('send')
            ->willReturn($response);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        $token = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->once())
            ->method('getToken')
            ->willReturn('mock_access_token');

        $url = $this->provider->getResourceOwnerDetailsUrl($token);

        $this->assertEquals('https://login.yandex.ru/info?format=jsono&auth_token=mock_access_token', $url);
    }

    public function testGetResourceOwner()
    {
        $response = json_decode('{"first_name":"mock_firstname","last_name":"mock_firstname","display_name":"mock_displayname","emails":["test@yandex.ru","other-test@yandex.ru"],"default_email":"test@yandex.ru","real_name":"Vasya Pupkin","birthday":"1987-03-12","default_avatar_id":"131652443","login":"mock_login","old_social_login":"uid-mmzxrnry","sex":"male","id":"1000034426"}', true);

        $token = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = $this->getMockBuilder(Yandex::class)
            ->setMethods(array('fetchResourceOwnerDetails'))
            ->getMock();

        $provider->expects($this->once())
            ->method('fetchResourceOwnerDetails')
            ->with($this->identicalTo($token))
            ->willReturn($response);

        /** @var YandexResourceOwner $resource */
        $resource = $provider->getResourceOwner($token);

        $this->assertInstanceOf(YandexResourceOwner::class, $resource);

        $this->assertEquals('1000034426', $resource->getId());
        $this->assertEquals('mock_login', $resource->getNickname());
        $this->assertEquals('test@yandex.ru', $resource->getEmail());
        $this->assertEquals('mock_displayname', $resource->getName());
        $this->assertEquals('mock_firstname', $resource->getFirstName());
        $this->assertEquals('mock_firstname', $resource->getLastName());
    }

    protected function setUp()
    {
        $this->provider = new Yandex([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri' => 'none',
        ]);
    }

    protected function tearDown()
    {
        $this->provider = null;
    }
}
