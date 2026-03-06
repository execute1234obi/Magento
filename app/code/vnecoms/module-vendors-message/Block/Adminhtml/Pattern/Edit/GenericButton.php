<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Block\Adminhtml\Pattern\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Vnecoms\VendorsMessage\Model\Pattern
     */
    protected $spamRepository;

    /**
     * @param Context $context
     * @param SpamRepositoryInterface $spamRepository
     */
    public function __construct(
        Context $context,
        \Vnecoms\VendorsMessage\Model\Pattern $spamRepository
    ) {
        $this->context = $context;
        $this->spamRepository = $spamRepository;
    }

    /**
     * Return Spam ID
     *
     * @return int|null
     */
    public function getPatternId()
    {
        try {
            return $this->spamRepository->load(
                $this->context->getRequest()->getParam('pattern_id')
            )->getId();
        } catch (NoSuchEntityException $e) {

        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
