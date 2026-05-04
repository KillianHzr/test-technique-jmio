<?php

namespace App\Controller;

use App\Dto\SearchFreelanceConsoDto;
use App\Service\FreelanceSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;


#[Route("/freelances", name: "freelances_")]
class FreelanceController extends AbstractController
{
    #[Route(name: "search", methods: ["GET"])]
    public function search(#[MapQueryParameter] SearchFreelanceConsoDto $dto, FreelanceSearchService $searchService): JsonResponse
    {
        $freelanceConsos = $searchService->searchFreelance($dto->query);
        return $this->json($freelanceConsos, Response::HTTP_OK, [], ["groups" => "freelance_conso"]);
    }
}