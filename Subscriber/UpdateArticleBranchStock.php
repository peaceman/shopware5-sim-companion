<?php

namespace n2305SimCompanion\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use n2305SimCompanion\Services\ArticleBranchStockUpdater;

class UpdateArticleBranchStock implements SubscriberInterface
{
    /** @var ArticleBranchStockUpdater */
    private $articleBranchStockUpdater;

    public function __construct(
        ArticleBranchStockUpdater $articleBranchStockUpdater
    ) {
        $this->articleBranchStockUpdater = $articleBranchStockUpdater;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'UpdateArticleBranchStock' => 'onUpdateArticleBranchStock',
        ];
    }

    public function onUpdateArticleBranchStock(Enlight_Event_EventArgs $e): void
    {
        $articleId = $e->get('articleId');
        $this->articleBranchStockUpdater->updateBranchStock($articleId);
    }
}
