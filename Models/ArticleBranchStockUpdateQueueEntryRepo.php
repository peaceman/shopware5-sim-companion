<?php

namespace n2305SimCompanion\Models;

use Doctrine\DBAL\DBALException;
use Shopware\Components\Model\ModelRepository;

class ArticleBranchStockUpdateQueueEntryRepo extends ModelRepository
{
    public function queueEntryForArticleIdExists(int $articleId): bool
    {
        $tableName = $this->getClassMetadata()->getTableName();

        return $this->getEntityManager()
            ->getConnection()
            ->fetchColumn(
                "select exists(select 1 from `$tableName` where article_id = :article_id)",
                ['article_id' => $articleId]
            ) === '1';
    }

    public function queueUpdateForArticleId(int $articleId): void
    {
        $tableName = $this->getClassMetadata()->getTableName();

        try {
            $this->getEntityManager()->getConnection()
                ->createQueryBuilder()
                ->insert($tableName)
                ->values([
                    'article_id' => ':article_id',
                    'created_at' => 'now()',
                ])
                ->setParameter('article_id', $articleId)
                ->execute();
        } catch (DBALException $e) {
            $prevE = $e->getPrevious();
            // catch mysql integrity constraint violation
            if (is_null($prevE) || $prevE->getCode() !== '23000') {
                throw $e;
            }
        }
    }
}
