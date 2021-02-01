<?php

namespace n2305SimCompanion\Models;

use DateTimeImmutable;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Detail;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="ArticleBranchStockRepo")
 * @ORM\Table(
 *     name="n2305_articles_branch_stocks",
 *     uniqueConstraints={@UniqueConstraint(name="nabs_ad_b_uq", columns={"article_detail_id", "branch"})}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class ArticleBranchStock extends ModelEntity
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
     * @var string
     *
     * @ORM\Column(name="branch", type="string", nullable=false)
     */
    private $branch;

    /**
     * @var int
     *
     * @ORM\Column(name="stock", type="integer", nullable=false)
     */
    private $stock;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(name="created_at", type="datetimetz_immutable", nullable=false)
     */
    private $createdAt;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(name="updated_at", type="datetimetz_immutable", nullable=true)
     */
    private $updatedAt;

    /**
     * @var Detail
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail", cascade={"remove"})
     * @ORM\JoinColumn(name="article_detail_id", referencedColumnName="id", onDelete="cascade", unique=false)
     */
    private $articleDetail;

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBranch(): string
    {
        return $this->branch;
    }

    public function setBranch(string $branch): void
    {
        $this->branch = $branch;
    }

    public function getStock(): int
    {
        return $this->stock ?? 0;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getArticleDetail(): Detail
    {
        return $this->articleDetail;
    }

    public function setArticleDetail(Detail $articleDetail): self
    {
        $this->articleDetail = $articleDetail;

        return $this;
    }
}
