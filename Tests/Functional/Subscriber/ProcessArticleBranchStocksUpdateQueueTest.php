<?php

namespace n2305SimCompanion\Tests\Functional\Subscriber;

use n2305SimCompanion\Models\ArticleBranchStockUpdateQueueEntry;
use n2305SimCompanion\Subscriber\ProcessArticleBranchStocksUpdateQueue;
use Psr\Log\NullLogger;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Tests\Functional\Components\Plugin\TestCase;

class ProcessArticleBranchStocksUpdateQueueTest extends TestCase
{
    /** @var ModelManager */
    private $modelManager;

    /** @var ContainerAwareEventManager */
    private $eventManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelManager = Shopware()->Container()->get('models');
        $this->modelManager->beginTransaction();

        $this->eventManager = Shopware()->Container()->get('events');
    }

    protected function tearDown(): void
    {
        $this->modelManager->rollback();

        parent::tearDown();
    }

    public function testDispatchesEventsPerQueueEntry(): void
    {
        $this->modelManager->getDBALQueryBuilder()
            ->insert('n2305_articles_branch_stocks_update_queue')
            ->values(['created_at' => 'now()', 'article_id' => 23])
            ->execute();

        $eventManager = $this->createMock(ContainerAwareEventManager::class);
        $eventManager
            ->expects(static::once())
            ->method('notify')
            ->with('UpdateArticleBranchStock', ['articleId' => 23]);

        $subscriber = new ProcessArticleBranchStocksUpdateQueue(new NullLogger(), $this->modelManager, $eventManager);
        $subscriber->onProcessQueue();
    }
}
