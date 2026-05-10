<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RecruiterController extends AbstractController
{
    #[Route('/recruiter/profile', name: 'app_recruiter_profile')]
    #[IsGranted('ROLE_RECRUITER')]
    public function profile(\Doctrine\ORM\EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $selection = $em->getRepository(\App\Entity\Selection::class)->findOneBy([
            'recruiter' => $user,
            'name' => 'Ma Sélection'
        ]);

        return $this->render('recruiter/profile.html.twig', [
            'selection' => $selection
        ]);
    }
}
