<?php

namespace App\EventSubscriber;

use Carbon\Carbon;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class TimestampSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {

        $entity = $args->getObject();
        $now = new Carbon();

        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt($now);
        } else {
            $reflection = new \ReflectionClass($entity);
            if ($reflection->hasProperty('updatedAt')) {
                $property = $reflection->getProperty('updatedAt');
                $property->setAccessible(true);
                if ($property->getValue($entity) === null) {
                    $property->setValue($entity, $now);
                }
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $now = new Carbon();

        if (method_exists($entity, 'setCreatedat')) {
            $entity->setCreatedAt($now);
        } else {
            $reflection = new \ReflectionClass($entity);
            if ($reflection->hasProperty('createdAt')) {
                $property = $reflection->getProperty('createdAt');
                $property->setAccessible(true);
                if ($property->getValue($entity) === null) {
                    $property->setValue($entity, $now);
                }
            }
        }

        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt($now);
        } else {
            $reflection = new \ReflectionClass($entity);
            if ($reflection->hasProperty('updatedAt')) {
                $property = $reflection->getProperty('updatedAt');
                $property->setAccessible(true);
                $property->setValue($entity, $now);
            }
        }
    }
}
