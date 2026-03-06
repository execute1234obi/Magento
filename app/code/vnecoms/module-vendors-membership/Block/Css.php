<?php

namespace Vnecoms\VendorsMembership\Block;

class Css extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Vnecoms\Membership\Helper\Data
     */
    protected $_membershipHelper;

    /**
     * Constructor.
     * 
     * @param \Vnecoms\Membership\Helper\Data                  $membershipHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Vnecoms\VendorsMembership\Helper\Data $membershipHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_membershipHelper = $membershipHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get package color.
     * 
     * @param int $package
     *
     * @return string
     */
    public function getPackageColor($package)
    {
        $color = $this->_membershipHelper->getPackageColor($package);

        return $color ? $color : '#5D6A9A';
    }
}
