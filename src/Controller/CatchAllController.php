<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatchAllController extends AbstractController
{
    #[Route('/{path}', name: 'catch_all', requirements: ['path' => '.*'], priority: -1)]
    public function __invoke(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
    }
}
