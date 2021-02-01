<?php

namespace n2305SimCompanion\SearchBundleDBAL\Condition;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class BranchAvailabilityConditionHandler implements ConditionHandlerInterface
{
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof BranchAvailabilityCondition;
    }

    public function generateCondition(ConditionInterface $condition, QueryBuilder $query, ShopContextInterface $context)
    {
        $query->innerJoin(
            'product',
            'n2305_articles_branch_stocks',
            'branchStocks',
            'branchStocks.article_detail_id = product.main_detail_id'
                . ' AND branchStocks.branch IN (:branches)'
                . ' AND branchStocks.stock > 0'
        );

        $query->setParameter(
            ':branches',
            $condition->getBranches(),
            Connection::PARAM_STR_ARRAY
        );
    }
}
