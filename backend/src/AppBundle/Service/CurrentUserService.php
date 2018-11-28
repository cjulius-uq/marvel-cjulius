<?php

namespace AppBundle\Service;

use GuzzleHttp\Client as HttpClient;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentUserService
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getFavouriteCharacters()
    {
        return $this->tokenStorage->getToken()->getUser()->getFavourites();
    }

    public function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}