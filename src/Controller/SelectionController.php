<?php

namespace App\Controller;

use App\Entity\FreelanceConso;
use App\Entity\Selection;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/selections')]
#[IsGranted('ROLE_RECRUITER')]
class SelectionController extends AbstractController
{
    #[Route('', name: 'api_selections_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $selections = $user->getSelections();

        $data = [];
        foreach ($selections as $selection) {
            $data[] = [
                'id' => $selection->getId(),
                'name' => $selection->getName(),
                'count' => $selection->getFreelances()->count()
            ];
        }

        return $this->json($data);
    }

    #[Route('/default', name: 'api_selections_default', methods: ['GET'])]
    public function defaultSelection(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $selection = $em->getRepository(Selection::class)->findOneBy(['recruiter' => $user, 'name' => 'Ma Sélection']);

        if (!$selection) {
            return $this->json(['id' => null, 'name' => 'Ma Sélection', 'freelances' => []]);
        }

        $freelances = [];
        foreach ($selection->getFreelances() as $f) {
            $freelances[] = [
                'id' => $f->getId(),
                'firstName' => $f->getFirstName(),
                'lastName' => $f->getLastName(),
                'jobTitle' => $f->getJobTitle(),
                'initials' => strtoupper(substr($f->getFirstName() ?? '', 0, 1) . substr($f->getLastName() ?? '', 0, 1))
            ];
        }

        return $this->json([
            'id' => $selection->getId(),
            'name' => $selection->getName(),
            'freelances' => $freelances
        ]);
    }

    #[Route('/ids', name: 'api_selections_ids', methods: ['GET'])]
    public function selectedIds(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $selection = $em->getRepository(Selection::class)->findOneBy(['recruiter' => $user, 'name' => 'Ma Sélection']);
        
        $ids = [];
        if ($selection) {
            foreach ($selection->getFreelances() as $f) {
                $ids[] = $f->getId();
            }
        }

        return $this->json($ids);
    }

    #[Route('/{id}', name: 'api_selections_detail', methods: ['GET'])]
    public function detail(int $id, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $selection = $em->getRepository(Selection::class)->findOneBy(['id' => $id, 'recruiter' => $user]);

        if (!$selection) {
            return $this->json(['error' => 'Selection not found'], Response::HTTP_NOT_FOUND);
        }

        $freelances = [];
        foreach ($selection->getFreelances() as $f) {
            $freelances[] = [
                'id' => $f->getId(),
                'firstName' => $f->getFirstName(),
                'lastName' => $f->getLastName(),
                'jobTitle' => $f->getJobTitle(),
                'initials' => strtoupper(substr($f->getFirstName() ?? '', 0, 1) . substr($f->getLastName() ?? '', 0, 1))
            ];
        }

        return $this->json([
            'id' => $selection->getId(),
            'name' => $selection->getName(),
            'freelances' => $freelances
        ]);
    }

    #[Route('/add-to-default/{freelanceId}', name: 'api_selections_add_default', methods: ['POST'])]
    public function addToDefault(int $freelanceId, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $freelance = $em->getRepository(FreelanceConso::class)->find($freelanceId);

        if (!$freelance) {
            return $this->json(['error' => 'Freelance not found'], Response::HTTP_NOT_FOUND);
        }

        $selection = $em->getRepository(Selection::class)->findOneBy(['recruiter' => $user, 'name' => 'Ma Sélection']);
        if (!$selection) {
            $selection = new Selection();
            $selection->setName('Ma Sélection');
            $selection->setRecruiter($user);
            $em->persist($selection);
        }

        $selection->addFreelance($freelance);
        $em->flush();

        return $this->json(['success' => true, 'count' => $selection->getFreelances()->count()]);
    }

    #[Route('/remove-from-default/{freelanceId}', name: 'api_selections_remove_default', methods: ['POST'])]
    public function removeFromDefault(int $freelanceId, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $freelance = $em->getRepository(FreelanceConso::class)->find($freelanceId);

        if (!$freelance) {
            return $this->json(['error' => 'Freelance not found'], Response::HTTP_NOT_FOUND);
        }

        $selection = $em->getRepository(Selection::class)->findOneBy(['recruiter' => $user, 'name' => 'Ma Sélection']);
        if ($selection) {
            $selection->removeFreelance($freelance);
            $em->flush();
        }

        return $this->json(['success' => true, 'count' => $selection ? $selection->getFreelances()->count() : 0]);
    }
}
