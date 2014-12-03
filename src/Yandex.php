<?php
namespace Aego\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\AbstractProvider;

class Yandex extends AbstractProvider
{
    public function urlAuthorize()
    {
        return 'https://oauth.yandex.ru/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://oauth.yandex.ru/token';
    }

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://login.yandex.ru/info?oauth_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User;
        $user->uid = $response->id;
        $user->nickname = $response->login;
        $user->email = isset($response->default_email)?$response->default_email:null;
        $user->firstName = isset($response->first_name)?$response->first_name:null;
        $user->lastName = isset($response->last_name)?$response->last_name:null;
        $user->name = isset($response->real_name)?$response->real_name:null;
        $user->gender = isset($response->sex)?$response->sex:null;
        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return $response->default_email;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return [$response->first_name, $response->last_name];
    }
}
