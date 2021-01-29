<?php

namespace n2305SimCompanion\Models;

use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Article\Detail;

class ArticleBranchStockRepo extends ModelRepository
{
    public function updateBranchStock(Detail $articleDetail, string $branch, int $stock): void
    {
        /** @var ?ArticleBranchStock $model */
        $model = $this->findOneBy(['articleDetail' => $articleDetail, 'branch' => $branch]);

        if (!$model) {
            $model = new ArticleBranchStock();
            $model->setBranch($branch);
            $model->setArticleDetail($articleDetail);
        }

        $model->setStock($stock);

        $this->getEntityManager()->persist($model);
    }
}
