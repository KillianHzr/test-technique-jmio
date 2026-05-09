<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChangeLocaleController extends AbstractController
{
    #[Route('/change-locale/{locale}', name: 'change_locale')]
    public function changeLocale(string $locale, Request $request): Response
    {
        $request->getSession()->set('_locale', $locale);
        
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('home');
    }
}
