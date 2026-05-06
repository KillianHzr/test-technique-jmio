<?php
namespace App\Service;

use App\Entity\Freelance;
use App\Entity\FreelanceConso;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Serializer\SerializerInterface;

readonly class FreelanceSerializer
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function serializeFreelance(Freelance $freelance, array $groups): string
    {
        return $this->serializer->serialize($freelance, 'json', ['groups' => $groups]);
    }

    #[ArrayShape([Freelance::class])]
    public function serializeFreelances(array $freelances, array $groups): string
    {
        return $this->serializer->serialize($freelances, 'json', ['groups' => $groups]);
    }

    #[ArrayShape([FreelanceConso::class])]
    public function serializeFreelancesConso(iterable $freelances, array $groups): string
    {
        if ($freelances instanceof \Traversable) {
            $freelances = iterator_to_array($freelances);
        }
        return $this->serializer->serialize($freelances, 'json', ['groups' => $groups]);
    }
}