<?php

namespace n2305SimCompanion\Tests\Functional\Services;

use n2305SimCompanion\Models\ArticleBranchStock;
use n2305SimCompanion\Services\ArticleBranchStockUpdater;
use Psr\Log\NullLogger;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Tests\Functional\Components\Plugin\TestCase;

class ArticleBranchStockUpdaterTest extends TestCase
{
    /** @var ModelManager */
    private $modelManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelManager = Shopware()->Container()->get('models');
        $this->modelManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->modelManager->rollback();

        parent::tearDown();
    }

    public function testStockUpdate(): void
    {
        // prepare test data
        $articleDetail = new Detail();
        $articleDetail->setNumber('dis is sku');
        $articleDetail->setAttribute(['availability' => json_encode([['branchNo' => '003', 'stock' => 4]])]);

        $articleDetails = [
            (new Detail())
                ->setNumber('sku 2')
                ->setAttribute([
                    'availability' => json_encode([
                        ['branchNo' => '003', 'stock' => 2],
                        ['branchNo' => '023', 'stock' => 42],
                    ]),
                ]),
            (new Detail())
                ->setNumber('sku 3')
                ->setAttribute([
                    'availability' => json_encode([
                        ['branchNo' => '003', 'stock' => 3],
                        ['branchNo' => '04', 'stock' => 1],
                    ]),
                ]),
        ];

        $article = new Article();
        $article->setName('dis is article');
        $article->setMainDetail($articleDetail);
        $article->setDetails($articleDetails);
        $this->modelManager->persist($article);
        $this->modelManager->flush();

        // exec test
        $updater = new ArticleBranchStockUpdater(new NullLogger(), $this->modelManager);
        $updater->updateBranchStock($article->getId());

        // assert
        $mainDetailBranchStocks = $this->modelManager
            ->getRepository(ArticleBranchStock::class)
            ->findBy(['articleDetail' => $article->getMainDetail()->getId()]);

        static::assertCount(3, $mainDetailBranchStocks);

        $stocksByBranchNo = array_reduce(
            $mainDetailBranchStocks,
            static function (array $carry, ArticleBranchStock $abs): array {
                $carry[$abs->getBranch()] = $abs->getStock();

                return $carry;
            },
            []
        );

        static::assertEquals(5, $stocksByBranchNo['003']);
        static::assertEquals(42, $stocksByBranchNo['023']);
        static::assertEquals(1, $stocksByBranchNo['04']);
    }
}
