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

use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/freelances', name: 'api_freelance_')]
class FreelanceController extends AbstractController
{
    public function __construct(
        private readonly FreelanceSearchService $freelanceSearchService,
        private readonly FreelanceSerializer $freelanceSerializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {}

    #[Route('', name: 'search', methods: ['GET'])]
    public function search(#[MapQueryString] SearchFreelanceConsoDto $dto = new SearchFreelanceConsoDto()): Response
    {
        $searchResult = $this->freelanceSearchService->searchFreelance($dto->query, $dto->page, $dto->limit, $dto->sort, $dto->skills);
        $json         = $this->freelanceSerializer->serialize($searchResult, ['freelance_conso']);

        return new Response($json, Response::HTTP_OK, [
            'Content-Type'  => 'application/json',
            'X-Total-Count' => (string) $searchResult->total,
            'X-Total-Pages' => (string) $searchResult->pages,
        ]);
    }

    #[Route('/skills', name: 'skills', methods: ['GET'])]
    public function skills(): Response
    {
        return $this->json($this->freelanceSearchService->getAllSkillsWithCounts());
    }

    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET'])]
    public function autocomplete(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $query = $request->query->get('q', '');
        if (strlen($query) < 3) {
            return $this->json(['suggestion' => '']);
        }

        $suggestion = $this->freelanceSearchService->getAutocompleteSuggestion($query);

        return $this->json(['suggestion' => $suggestion]);
    }

    #[Route('/{id}/matching', name: 'matching', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function matching(int $id): Response
    {
        try {
            $results = $this->freelanceSearchService->getMatchingFreelances($id);
            $json = $this->freelanceSerializer->serializeFreelancesConso($results, ['freelance_conso']);

            return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id): Response
    {
        $freelance = $this->entityManager->getRepository(FreelanceConso::class)->find($id);

        if (!$freelance) {
            return $this->json(['error' => $this->translator->trans('Freelance not found')], Response::HTTP_NOT_FOUND);
        }

        return new Response(
            $this->freelanceSerializer->serializeFreelancesConso([$freelance], ['freelance_conso']),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}
