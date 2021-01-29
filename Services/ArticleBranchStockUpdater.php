<?php

namespace n2305SimCompanion\Services;

use n2305SimCompanion\Models\ArticleBranchStock;
use n2305SimCompanion\Models\ArticleBranchStockRepo;
use Psr\Log\LoggerInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;

class ArticleBranchStockUpdater
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ModelManager */
    private $modelManager;

    public function __construct(
        LoggerInterface $logger,
        ModelManager $modelManager
    ) {
        $this->logger = $logger;
        $this->modelManager = $modelManager;
    }

    public function updateBranchStock(int $articleId): void
    {
        $this->logger->info('Updating branch stock', ['articleId' => $articleId]);

        /** @var Article $article */
        $article = $this->modelManager->getRepository(Article::class)->findOneBy(['id' => $articleId]);
        if (!$article) {
            $this->logger->error('Failed to find article', ['articleId' => $articleId]);
            return;
        }

        $stockByBranch = array_reduce(
            $article->getDetails()->toArray(),
            static function (array $carry, Detail $articleDetail): array {
                $availability = json_decode((string) $articleDetail->getAttribute()->getAvailability(), true);
                if (!is_array($availability)) return $carry;

                foreach ($availability as $branchData) {
                    if (!is_array($branchData)) continue;
                    if (!isset($branchData['branchNo'], $branchData['stock'])) continue;

                    $carry[$branchData['branchNo']] += (int) $branchData['stock'];
                }

                return $carry;
            },
            []
        );

        /** @var ArticleBranchStockRepo $branchStockRepo */
        $branchStockRepo = $this->modelManager->getRepository(ArticleBranchStock::class);
        foreach ($stockByBranch as $branch => $stock) {
            $branchStockRepo->updateBranchStock($article->getMainDetail(), $branch, $stock);
        }

        $this->modelManager->flush();

        $this->logger->info('Updated branch stock', ['articleId' => $articleId, 'stockByBranch' => $stockByBranch]);
    }
}
