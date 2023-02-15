<?php
declare(strict_types=1);

namespace Deloitte\PayMe\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PayMeSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get list
     *
     * @return \Deloitte\PayMe\Api\Data\PayMeInterface[]
     */
    public function getItems();

    /**
     * Set list
     *
     * @param \Deloitte\PayMe\Api\Data\PayMeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
