<?php

namespace n2305SimCompanion\Tests\Functional\Subscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use n2305SimCompanion\Models\ArticleBranchStockUpdateQueueEntry;
use n2305SimCompanion\Models\ArticleBranchStockUpdateQueueEntryRepo;
use n2305SimCompanion\Subscriber\ArticleDetailSubscriber;
use Psr\Log\NullLogger;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Tests\Functional\Components\Plugin\TestCase;

class ArticleDetailSubscriberTest extends TestCase
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

    public function testArticleDetailSubscriber(): void
    {
        $articleDetail = new Detail();
        $articleDetail->setNumber('dis is sku');

        $article = new Article();
        $article->setName('dis is article');
        $article->setMainDetail($articleDetail);
        $this->modelManager->persist($article);
        $this->modelManager->flush();

        $article = $this->modelManager->getRepository(Article::class)->find($article->getId());

        $subscriber = new ArticleDetailSubscriber(new NullLogger(), $this->modelManager);
        $subscriber->postUpdate(new LifecycleEventArgs($articleDetail, $this->modelManager));
        $subscriber->postPersist(new LifecycleEventArgs($articleDetail, $this->modelManager));

        /** @var ArticleBranchStockUpdateQueueEntryRepo $updateQueueRepo */
        $updateQueueRepo = $this->modelManager->getRepository(ArticleBranchStockUpdateQueueEntry::class);
        $queueEntryExists = $updateQueueRepo->queueEntryForArticleIdExists($article->getId());
        static::assertTrue($queueEntryExists);
    }
}
