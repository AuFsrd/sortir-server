<?php

namespace App\Services;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Metaclass\FilterBundle\Filter\FilterLogic;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Doctrine\ORM\Query\Expr\Join;

class CustomFilterLogic extends FilterLogic implements FilterInterface
{
    private $filterLocator;
    /** @var string Filter classes must match this to be applied with logic */
    private $classExp;
    /** @var FilterInterface[] */
    private $filters;
    /** @var bool Wheather to replace all inner joins by left joins */
    private $innerJoinsLeft;

    /**
     * @param ContainerInterface $filterLocator
     * @param $regExp string Filter classes must match this to be applied with logic
     * @param $innerJoinsLeft bool Wheather to replace all inner joins by left joins.
     *   This makes the standard Api Platform filters combine properly with OR,
     *   but also changes the behavior of ExistsFilter =false.
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $filterLocator, ManagerRegistry $managerRegistry, ?LoggerInterface $logger = null, ?array $properties = null, ?NameConverterInterface $nameConverter = null, string $classExp='//', $innerJoinsLeft=false)
    {
        parent::__construct($filterLocator, $managerRegistry, $logger, $properties, $nameConverter, $classExp, $innerJoinsLeft);
    }
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if (!isset($context['filters']) || !\is_array($context['filters'])) {
            throw new \InvalidArgumentException('::apply without $context[filters] not supported');
        }

        $this->filters = $this->getFilters($operation);

        $logic = false; #15 when no where filter is used, do not replace inner joins by left joins
        if (isset($context['filters']['and']) ) {
            $expressions = $this->filterProperty('and', $context['filters']['and'], $queryBuilder, $queryNameGenerator, $resourceClass, $operation, $context);
            foreach($expressions as $exp) {
                $queryBuilder->andWhere($exp);
                $logic = true;
            };
        }
        if (isset($context['filters']['not']) ) {
            // NOT expressions are combined by parent logic, here defaulted to AND
            $expressions = $this->filterProperty('not', $context['filters']['not'], $queryBuilder, $queryNameGenerator, $resourceClass, $operation, $context);
            foreach($expressions as $exp) {
                $queryBuilder->andWhere(new Expr\Func('NOT IN', [$exp]));
                $logic = true;
            };
        }
        #Issue 10: for security allways AND with existing criteria
        if (isset($context['filters']['or'])) {
            $expressions = $this->filterProperty('or', $context['filters']['or'], $queryBuilder, $queryNameGenerator, $resourceClass, $operation, $context);
            if (!empty($expressions)) {
                $queryBuilder->andWhere(new Expr\Orx($expressions));
                $logic = true;
            }
        }

        if ($this->innerJoinsLeft && $logic) {
            $this->replaceInnerJoinsByLeftJoins($queryBuilder);
        }
    }

}