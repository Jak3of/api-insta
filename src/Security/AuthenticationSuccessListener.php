<?php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AuthenticationSuccessListener implements AuthenticationSuccessHandlerInterface
{
    private $jwtManager;
    private $normalizer;

    public function __construct(JWTTokenManagerInterface $jwtManager, NormalizerInterface $normalizer)
    {
        $this->jwtManager = $jwtManager;
        $this->normalizer = $normalizer;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) : JsonResponse
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return new JsonResponse([
                'message' => 'Invalid user',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $jwt = $this->jwtManager->create($user);

        $userarray = $this->normalizer->normalize($user, null, ['groups' => 'user:identifier']);

        $data = array(
            'token' => $jwt,
            'user' => $userarray
        );

        return new JsonResponse($data, Response::HTTP_OK);
    }
}
