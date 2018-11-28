<?php

namespace AppBundle\Service;

use GuzzleHttp\Client as HttpClient;
use AppBundle\Repository\MarvelCharacterRepository;
use Doctrine\ORM\EntityManager;

class CharacterService
{
    private $entityManager;
    private $currentUserService;
    private $characterRepo;

    public function __construct(EntityManager $entityManager, $currentUserService, MarvelCharacterRepository $characterRepo)
    {
        $this->entityManager = $entityManager;
        $this->currentUserService = $currentUserService;
        $this->characterRepo = $characterRepo;
    }

    public function find(int $id)
    {
        $character = $this->characterRepo->find($id);

        if ($character) {
            return $this->attachFavourites($character);
        }
        return null;
    }

    public function findById(int $id)
    {
        $characters = $this->characterRepo->findById($id);

        if (count($characters)) {
            return $this->attachFavourites($characters);
        }
        return [];
    }

    public function findByFriendlyName(string $friendlyName)
    {
        $characters = $this->characterRepo->findByFriendlyName($friendlyName);

        if (count($characters)) {
            return $this->attachFavourites($characters);
        }
        return [];
    }

    private function findByFavourites(array $criteria, $orderBy = null, $limit = null, $offset = null)
    {
        $favourites = $this->currentUserService->getFavouriteCharacters();
        $favourites = array_slice($favourites->toArray(), $offset, $limit);

        return $favourites;
    }

    public function findBy(array $criteria, $orderBy = null, $limit = null, $offset = null)
    {
        if (isset($criteria['favourites']) && $criteria['favourites'] === true) {
            return $this->findByFavourites($criteria, $orderBy, $limit, $offset);
        }

        // Get characters and favourite
        $characters = $this->characterRepo->findBy($criteria, $orderBy, $limit, $offset);

        if (count($characters)) {
            return $this->attachFavourites($characters);
        }
        return [];
    }

    public function save($character) {
        $this->entityManager->persist($character);
        $this->entityManager->flush();
    }

    private function attachFavourites($characters)
    {
        $arrayConversion = false;
        if (!is_array($characters)) {
            $arrayConversion = true;
            $characters = [$characters];
        }

        $favourites = $this->currentUserService->getFavouriteCharacters();
        /*$favouriteIds = array_map(function($character) {
            return $character->getId();
        }, $favourites->toArray());*/

        for ($iChar = 0; $iChar < count($characters);  $iChar++) {
            $character = $characters[$iChar];

            /*if (array_search($character->getId(), $favouriteIds) !== false) {
                $characters[$iChar]->setFavourite(true); // Manually set flag
            }*/
            if ($favourites->contains($character)) {
                $characters[$iChar]->setFavourite(true); // Manually set flag
            }
        }

        if ($arrayConversion) {
            return $characters[0];
        }
        return $characters;
    }
}