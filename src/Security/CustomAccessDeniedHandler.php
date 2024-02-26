<?php
    // src/Security/CustomAccessDeniedHandler.php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomAccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $accessDeniedException) : JsonResponse
    {
        $data = [
            'message' => 'Acceso denegado. No tienes permisos para acceder a esta área.',
            'code' => JsonResponse::HTTP_FORBIDDEN,
        ];

        return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
    }
}
