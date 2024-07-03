<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Schema;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private SerializerInterface $serializer)
    {
    }
    #[Route('/registration', name: 'registration', methods: 'POST')]
    #[OA\Post(
        path:"/api/registration",
        summary:"Inscription d'un nouvel utilisateur",
        requestBody: new RequestBody(
            required: true,
            description: "Données de l'utilisateur à inscrire",
            content: [new MediaType(mediaType: "application/json",
            schema: new Schema(type: "object", properties:[new Property(
                property: "email",
                type: "string",
                example: "adresse@mail.com"
                ),
        new Property(
            property: "password",
            type: "string",
            example: "mot de passe"
        )]))]
        ),
    )]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            ['user'=>$user->getUserIdentifier(), 'apiToken'=> $user->getApiToken(), 'roles'=>$user->getRoles()],
        Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        path:"/api/login",
        summary:"Connecter un utilisateur",
        requestBody: new RequestBody(
            required: true,
            description: "Données de l'utilisateur à inscrire",
            content: [new MediaType(mediaType: "application/json",
                schema: new Schema(type: "object", properties:[new Property(
                    property: "username",
                    type: "string",
                    example: "adresse@mail.com"
                ),
                    new Property(
                        property: "password",
                        type: "string",
                        example: "mot de passe"
                    )]))]
        ),
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
             return new JsonResponse(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);

 }
        return new JsonResponse([
            'user'=>$user->getUserIdentifier(),
            'apiToken'=> $user->getApiToken(),
            'roles'=> $user->getRoles(),
        ]);
    }
}
