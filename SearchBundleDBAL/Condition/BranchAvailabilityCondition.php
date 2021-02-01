<?php

namespace n2305SimCompanion\SearchBundleDBAL\Condition;

use Assert\Assertion;
use Shopware\Bundle\SearchBundle\ConditionInterface;

class BranchAvailabilityCondition implements ConditionInterface, \JsonSerializable
{
    private const NAME = 'n2305_sim_companion_search_bundle_branch_availability';

    /** @var string[] */
    protected $branches;

    public function __construct(array $branches)
    {
        Assertion::allString($branches);
        $this->branches = $branches;
        sort($this->branches, SORT_STRING);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function getBranches(): array
    {
        return $this->branches;
    }
}
