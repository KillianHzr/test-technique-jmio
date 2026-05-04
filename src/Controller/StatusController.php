<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route("/status", name: "status_")]
class StatusController extends AbstractController
{
    #[Route("/up", name: "up", methods: ["POST"])]
    public function status(): JsonResponse
    {
        return $this->json(['status' => 'ok'], Response::HTTP_OK);
    }
}