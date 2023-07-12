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

        $arrayValue = json_decode($value);
        $alias = $queryBuilder->getRootAliases()[0];
        $user = $queryBuilder->getEntityManager()->getRepository(User::class)->find($arrayValue[0]);

        $queryBuilder->leftJoin(sprintf('%s.participants', $alias), 'participant');

        if (in_array('organiser', $arrayValue, true)) {
            $queryBuilder
                ->orWhere(sprintf('%s.organiser = :userId', $alias));
        }

        if (in_array('isParticipant', $arrayValue, true)) {
            $queryBuilder
                ->orWhere(sprintf(':userId MEMBER OF %s.participants', $alias));
        }

        if (in_array('notParticipant', $arrayValue, true)) {
            $queryBuilder
                ->orWhere(sprintf(':userId NOT MEMBER OF %s.participants', $alias));
        }

        $queryBuilder->setParameter('userId', $user->getId());
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