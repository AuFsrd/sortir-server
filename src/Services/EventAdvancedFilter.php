<?php

namespace App\Services;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class EventAdvancedFilter extends AbstractFilter
{

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void
    {
        if ($property != 'whereUser') {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $userId = $value[0];

        $subQueryBuilder = $queryBuilder->getEntityManager()->createQueryBuilder();
        $subQueryBuilder->select('event.id')
            ->from(Event::class, 'event')
            ->leftJoin('event.participants', 'user');
        if (isset($value[1]))

        $queryBuilder->andWhere($queryBuilder->expr()->notIn("$rootAlias.id", $subQueryBuilder->getDQL()))
            ->setParameter('userId', $userId);

    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'notParticipant' => [
                'property' => 'whereUser',
                'type' => Type::BUILTIN_TYPE_ARRAY,
                'required' => false,
                'description' => 'Filtre les events en fonction du user',
                'openapi' => [
                    'allowReserved' => false,
                    'allowEmptyValue' => true,
                    'explode' => false,
                ]
            ]
        ];
    }
}