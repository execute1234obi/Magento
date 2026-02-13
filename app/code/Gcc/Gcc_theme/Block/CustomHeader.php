<?php
namespace Gcc\Gcc_theme\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class CustomHeader extends Template
{
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomerName()
    {
        return $this->isLoggedIn()
            ? $this->customerSession->getCustomer()->getFirstname()
            : null;
    }

    public function getLogoutUrl()
    {
        return $this->getUrl('customer/account/logout');
    }

    public function getLoginUrl()
    {
        return $this->getUrl('customer/account/login');
    }

    public function getRegisterUrl()
    {
        return $this->getUrl('customer/account/create');
    }
}
