<?php

namespace n2305SimCompanion\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use n2305SimCompanion\Models\ArticleBranchStockUpdateQueueEntry;
use n2305SimCompanion\Models\ArticleBranchStockUpdateQueueEntryRepo;
use Psr\Log\LoggerInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;

class ArticleDetailSubscriber implements EventSubscriber
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->handleModelEvent($args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->handleModelEvent($args);
    }

    private function handleModelEvent(LifecycleEventArgs $args): void
    {
        $model = $args->getEntity();
        if (!($model instanceof Detail))
            return;

        $this->queueForBranchStockUpdate($args->getEntityManager(), $model);
    }

    private function queueForBranchStockUpdate(ModelManager $modelManager, Detail $articleDetail): void
    {
        /** @var ArticleBranchStockUpdateQueueEntryRepo $updateQueueRepo */
        $updateQueueRepo = $modelManager->getRepository(ArticleBranchStockUpdateQueueEntry::class);

        $articleId = $articleDetail->getArticleId() ?? $articleDetail->getArticle()->getId();
        $updateQueueRepo->queueUpdateForArticleId($articleId);

        $this->logger->debug('Enqueue article for branch stock update', [
            'articleId' => $articleId,
            'articleDetailId' => $articleDetail->getId(),
        ]);
    }
}
