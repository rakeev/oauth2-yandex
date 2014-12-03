<?php
namespace Aego\OAuth2\Client\Test\Provider;

class YandexTest extends \PHPUnit_Framework_TestCase
{
    protected $response;
    protected $provider;
    protected $token;

    protected function setUp()
    {
        $this->response = json_decode('{"id": "12345678", "login": "username", "display_name": "dpname", "sex": "male",'
            .' "first_name": "\u0418\u043C\u044F", "last_name": "\u0424\u0430\u043C\u0438\u043B\u0438\u044F",'
            .' "real_name": "\u0418\u043C\u044F \u0424\u0430\u043C\u0438\u043B\u0438\u044F",'
            .' "emails": ["login@yandex.ru"], "default_email": "login@yandex.ru"}');
        $this->provider = new \Aego\OAuth2\Client\Provider\Yandex([
            'clientId' => 'mock',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
        $this->token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'mock_token',
        ]);
    }

    public function testUrlUserDetails()
    {
        $query = parse_url($this->provider->urlUserDetails($this->token), PHP_URL_QUERY);
        parse_str($query, $param);

        $this->assertEquals($this->token->accessToken, $param['oauth_token']);
    }

    public function testUserDetails()
    {
        $user = $this->provider->userDetails($this->response, $this->token);
        $this->assertInstanceOf('League\\OAuth2\\Client\\Entity\\User', $user);
        $this->assertEquals($this->response->id, $user->uid);
        $this->assertEquals($this->response->login, $user->nickname);
        $this->assertEquals($this->response->default_email, $user->email);
        $this->assertEquals($this->response->sex, $user->gender);
        $this->assertEquals('Имя', $user->firstName);
        $this->assertEquals('Фамилия', $user->lastName);
        $this->assertEquals('Имя Фамилия', $user->name);
    }

    public function testUserDetailsEmpty()
    {
        unset($this->response->real_name, $this->response->first_name, $this->response->last_name);
        unset($this->response->default_email, $this->response->emails, $this->response->sex);
        $user = $this->provider->userDetails($this->response, $this->token);
        $this->assertEmpty($user->gender);
        $this->assertEmpty($user->email);
        $this->assertEmpty($user->name);
    }

    public function testUserUid()
    {
        $uid = $this->provider->userUid($this->response, $this->token);
        $this->assertEquals($this->response->id, $uid);
    }

    public function testUserEmail()
    {
        $email = $this->provider->userEmail($this->response, $this->token);
        $this->assertEquals($this->response->default_email, $email);
    }

    public function testUserScreenName()
    {
        $name = $this->provider->userScreenName($this->response, $this->token);
        $this->assertEquals([$this->response->first_name, $this->response->last_name], $name);
    }
}
