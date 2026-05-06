<?php

namespace App\Controller;

use App\Dto\SearchFreelanceConsoDto;
use App\Entity\FreelanceConso;
use App\Service\FreelanceSearchService;
use App\Service\FreelanceSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/freelances', name: 'api_freelance_')]
class FreelanceController extends AbstractController
{
    public function __construct(
        private readonly FreelanceSearchService $freelanceSearchService,
        private readonly FreelanceSerializer $freelanceSerializer,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('', name: 'search', methods: ['GET'])]
    public function search(#[MapQueryString] SearchFreelanceConsoDto $dto = new SearchFreelanceConsoDto()): Response
    {
        $searchResult = $this->freelanceSearchService->searchFreelance($dto->query, $dto->page, $dto->limit, $dto->sort);
        $json         = $this->freelanceSerializer->serializeFreelancesConso($searchResult->results, ['freelance_conso']);

        return new Response($json, Response::HTTP_OK, [
            'Content-Type'  => 'application/json',
            'X-Total-Count' => (string) $searchResult->total,
            'X-Total-Pages' => (string) $searchResult->pages,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id): Response
    {
        $freelance = $this->entityManager->getRepository(FreelanceConso::class)->find($id);

        if (!$freelance) {
            return $this->json(['error' => 'Freelance not found'], Response::HTTP_NOT_FOUND);
        }

        return new Response(
            $this->freelanceSerializer->serializeFreelancesConso([$freelance], ['freelance_conso']),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}
