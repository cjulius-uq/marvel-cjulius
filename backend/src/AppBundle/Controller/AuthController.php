<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class AuthController extends Controller
{
    /**
     * @Route("/auth/generateToken", methods={"POST"})
     */
    public function generateTokenAction(Request $req)
    {
        $username = $req->get('username');
        $password = $req->get('password');

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['username' => $username]);
        if (!$user) {
            throw new BadCredentialsException("Your username or password was incorrect. Please try again.", Response::HTTP_UNAUTHORIZED);
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordvalid($user, $password);
        if (!$isValid) {
            throw new BadCredentialsException("Your username or password was incorrect. Please try again.", Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->get('lexik_jwt_authentication.encoder')->encode([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'exp' => time() + 3600
        ]);

        return new JsonResponse(['token' => $token]);
    }

    /**
     * @Route("/auth/checkToken")
     */
    public function checkTokenAction(Request $req)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Your token is invalid.');

        return  new JsonResponse(['valid' => true]);
    }

}
