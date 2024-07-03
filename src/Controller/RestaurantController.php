<?php
namespace App\Controller;

    use App\Entity\Restaurant;
    use App\Repository\RestaurantRepository;
    use DateTimeImmutable;
    use Doctrine\ORM\EntityManagerInterface;
    use OpenApi\Attributes\MediaType;
    use OpenApi\Attributes\Property;
    use OpenApi\Attributes\RequestBody;
    use OpenApi\Attributes\Schema;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
    use Symfony\Component\Serializer\SerializerInterface;
    use OpenApi\Attributes as OA;


    #[Route('api/restaurant', name: 'app_api_restaurant_')]
class RestaurantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private RestaurantRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ){
    }

    #[Route(methods: ['POST'])]
    #[OA\Post(
        path:"/api/restaurant",
        summary:"Créer un restaurant",
        tags: ["Restaurant"],
        requestBody:
        new RequestBody(
            required: true,
            description: "Données du restaurant à créer",
            content:
            [new MediaType(
                mediaType: "application/json",
                schema:
            new Schema(
                type: "object",
                properties:
                [new Property(
                    property: "name",
                    type: "string",
                    example: "Nom du restaurant"
                ),
                    new Property(
                        property: "description",
                        type: "string",
                        example: "description du restaurant"
    )]))]
    ),
    )]
    public function new(Request $request): JsonResponse
    {
        $restaurant = $this->serializer->deserialize($request->getContent(), Restaurant::class, 'json');
        $restaurant->setCreatedAt(new DateTimeImmutable());

        $this->em->persist($restaurant);
        $this->em->flush();

        $responseData = $this->serializer->serialize($restaurant, 'json');
        $location = $this->urlGenerator->generate(
            'app_restaurant_show',
            ['id' => $restaurant->getId()],
         UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=>$location], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path:"/api/restaurant/{id}",
        summary:"Afficher un restaurant par son ID",
        tags: ["Restaurant"]
    )]


    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);
        if ($restaurant) {
            $responseData = $this->serializer->serialize($restaurant, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);
        if (!$restaurant) {
            $restaurant = $this->serializer->deserialize(
                $request->getContent(),
                Restaurant::class,
                'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $restaurant]
            );
            $restaurant->setUpdatedAt(new DateTimeImmutable());

            $this->em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);
        if (!$restaurant) {
            $this->em->remove($restaurant);
            $this->em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}

