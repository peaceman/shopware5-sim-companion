<?php

namespace n2305SimCompanion\Models;

use DateTimeImmutable;
use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Models\Article\Article;

/**
 * @ORM\Entity(repositoryClass="ArticleBranchStockUpdateQueueEntryRepo")
 * @ORM\Table(name="n2305_articles_branch_stocks_update_queue")
 * @ORM\HasLifecycleCallbacks
 */
class ArticleBranchStockUpdateQueueEntry extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Article
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article", cascade={"remove"})
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade", unique=true)
     */
    private $article;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(name="created_at", type="datetimetz_immutable", nullable=false)
     */
    private $createdAt;

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
