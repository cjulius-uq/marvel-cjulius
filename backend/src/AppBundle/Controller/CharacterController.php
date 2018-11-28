<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CharacterController extends Controller
{
    /**
     * @Route("/characters/{id}", methods={"GET"})
     */
    public function getAction(Request $req, $id)
    {
        $friendlyName = $req->get('friendlyName');

        // Find the character
        $characterFinder = $this->get('app.character_service');

        if ($id) {
            $characters = $characterFinder->findById($id);
        } else if ($friendlyName) {
            $characters = $characterFinder->findByFriendlyName($friendlyName);
        } else {
            throw new \Exception("Please specify a search criteria");
        }

        if (!count($characters)) {
            throw $this->createNotFoundException("Could not find character");
        }

        $character = $characters[0];

        return $this->serialize($character);
    }

    /**
     * @Route("/characters/{id}", methods={"PUT"})
     */
    public function saveAction(Request $req, $id)
    {
        // We only allow saving of favourites, no other properties may be changed by the client
        try {
            $character = json_decode($req->getContent())->character;
        } catch (\Exception $e) {
            throw new \Exception("Could not parse input");
        }
        $favourite = $character->favourite;

        // Find the character locally
        $characterFinder = $this->get('app.character_service');
        $localCharacter = $characterFinder->find($id);
        if (!$localCharacter) {
            throw $this->createNotFoundException("Could not find any characters");
        }

        $user = $this->getUser();

        if ($favourite) {
            $user->addFavourite($localCharacter);
        } else {
            $user->removeFavourite($localCharacter);
        }

        // Persist changes to DB
        $characterFinder->save($localCharacter);

        // Make local change to object for quick frontend update
        $localCharacter->setFavourite($favourite);

        return $this->serialize($localCharacter);
    }

    private function serialize($characters)
    {
        $multiple = is_array($characters);

        // Serialize and return
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        // Build the response object
        $returnData = [];
        if ($multiple) {
            foreach ($characters as $character) {
                $returnData[] = json_decode($serializer->serialize($character, 'json'));
            }
        } else {
            $returnData = json_decode($serializer->serialize($characters, 'json'));
        }

        return new JsonResponse([ $multiple ? 'characters' : 'character' => $returnData ]);

    }

        /**
     * @Route("/characters")
     */
    public function listAction(Request $req)
    {
        // Return a single model if filtering by friendlyName
        $friendlyName = $req->get('friendlyName');
        if ($friendlyName) {
            return $this->getAction($req, null);
        }

        // Get options
        $offset = $req->get('offset');
        $limit = $req->get('limit');
        $orderField = $req->get('orderField');
        $orderDirection = $req->get('orderDirection');
        if ($orderField) {
            $orderDirection = isset($orderDirection) ? $orderDirection : 'DESC';
            $orderBy = [$orderField => $orderDirection];
        } else {
            $orderBy = null;
        }

        // Optional filters
        $filters = [];
        if ($req->get('favourites')) {
            $filters['favourites'] = true;
        }

        // Perform search
        $characterFinder = $this->get('app.character_service');
        $characters = $characterFinder->findBy($filters, $orderBy, $limit ? $limit : 20, $offset);
        if (!count($characters)) {
            throw $this->createNotFoundException("Could not find any characters");
        }

        // Serialize and return
        return $this->serialize($characters);
    }

}
