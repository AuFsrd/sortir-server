<?php

namespace App\Services;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use App\Entity\Event;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\ORM\QueryBuilder;

class EventFilter extends AbstractFilter
{

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ($property != 'notParticipant') {
            return;
        }

        $user = $queryBuilder->getEntityManager()->getRepository(User::class)->find($value);

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $userId = $value;

        $subQueryBuilder = $queryBuilder->getEntityManager()->createQueryBuilder();
        $subQueryBuilder->select('event.id')
            ->from(Event::class, 'event')
            ->leftJoin('event.participants', 'user')
            ->where($subQueryBuilder->expr()->eq('user.id', ':userId'));

        $queryBuilder->andWhere($queryBuilder->expr()->notIn("$rootAlias.id", $subQueryBuilder->getDQL()))
            ->setParameter('userId', $userId);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'notParticipant' => [
                'property' => 'notParticipant',
                'type' => 'integer',
                'required' => false,
                'description' => 'Retourne les event ou l\'utilisateur n\'est pas inscrit',
                'openapi' => [
                    'allowReserved' => false,
                    'allowEmptyValue' => true,
                    'explode' => false,
                ]
            ]
        ];
    }
}