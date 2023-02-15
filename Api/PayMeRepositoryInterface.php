<?php

declare(strict_types=1);

namespace Deloitte\PayMe\Api;

interface PayMeRepositoryInterface
{
    /**
     * Save
     *
     * @param \Deloitte\PayMe\Api\Data\PayMeInterface $payMe
     * @return \Deloitte\PayMe\Api\Data\PayMeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\PayMeInterface $payMe);

    /**
     * Retrieve
     *
     * @param int $id
     * @return \Deloitte\PayMe\Api\Data\PayMeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Deloitte\PayMe\Api\Data\PayMeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
    
}
