<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocumentationController extends AbstractController
{
    #[Route('/documentation/api', name: 'app_documentation_api')]
    public function index(): Response
    {
        return $this->render('documentation/api.html.twig');
    }
}
