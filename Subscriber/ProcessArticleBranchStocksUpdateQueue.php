<?php

namespace n2305SimCompanion\Subscriber;

use Enlight\Event\SubscriberInterface;
use n2305SimCompanion\Models\ArticleBranchStockUpdateQueueEntry;
use Psr\Log\LoggerInterface;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\Model\ModelManager;
use Throwable;

class ProcessArticleBranchStocksUpdateQueue implements SubscriberInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ModelManager */
    private $modelManager;

    /** @var ContainerAwareEventManager */
    private $eventManager;

    public function __construct(
        LoggerInterface $logger,
        ModelManager $modelManager,
        ContainerAwareEventManager $eventManager
    ) {
        $this->logger = $logger;
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_ProcessArticleBranchStocksUpdateQueue' => 'onProcessQueue',
        ];
    }

    public function onProcessQueue(): void
    {
        $queueRepo = $this->modelManager->getRepository(ArticleBranchStockUpdateQueueEntry::class);
        $entries = $queueRepo->findBy([], ['id' => 'asc'], 100);

        /** @var ArticleBranchStockUpdateQueueEntry $entry */
        foreach ($entries as $entry) {
            $this->processQueueEntry($entry);
        }
    }

    private function processQueueEntry(ArticleBranchStockUpdateQueueEntry $queueEntry): void
    {
        $loggingContext = [
            'id' => $queueEntry->getId(),
            'createdAt' => $queueEntry->getCreatedAt()->format(DATE_RFC3339),
            'articleId' => $queueEntry->getArticle()->getId(),
        ];

        try {
            $this->logger->info('Processing queue entry', $loggingContext);

            $this->modelManager->transactional(function () use ($queueEntry) {
                $articleId = $queueEntry->getArticle()->getId();

                $this->eventManager->notify('UpdateArticleBranchStock', ['articleId' => $articleId]);

                $this->modelManager->remove($queueEntry);
            });
        } catch (Throwable $e) {
            $this->logger->error('Failed processing queue entry', array_merge($loggingContext, [
                'e' => $e,
            ]));
        }
    }
}
