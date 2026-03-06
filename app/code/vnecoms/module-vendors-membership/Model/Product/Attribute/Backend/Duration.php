<?php

namespace Vnecoms\VendorsMembership\Model\Product\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;

class Duration extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    public function __construct(
        \Magento\Framework\Locale\FormatInterface $localeFormat
    ) {
        $this->_localeFormat = $localeFormat;
    }

    /**
     * Validate object.
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return bool
     *
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate($object)
    {
        parent::validate($object);
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);

        if ($value && is_array($value)) {
            $optionCount = 0;
            foreach ($value as $duration) {
                if (isset($duration['delete']) && $duration['delete']) {
                    continue;
                }
                ++$optionCount;
                if (!isset($duration['duration'])
                    || !isset($duration['duration_unit'])
                    || !isset($duration['price'])
                    || !$duration['duration']
                    || !$duration['duration_unit']
                    || $duration['price'] == ""
                ) {
                    throw new LocalizedException(__("Duration, Duration Unit and Price must be set '%1'", $attrCode));
                }
            }

            if (!$optionCount) {
                throw new LocalizedException(__("Membership duration must be set '%1'", $attrCode));
            }
        } else {
            throw new LocalizedException(__("Membership duration must be set '%1'", $attrCode));
        }
    }
    /**
     * Sort values.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _sortValues($data)
    {
        usort($data, [$this, '_sortMembershipDurations']);

        return $data;
    }

    /**
     * Sort tier price values callback method.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _sortMembershipDurations($a, $b)
    {
        if ($a['sort_order'] != $b['sort_order']) {
            return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Before save method.
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();

        $value = $object->getData($attrCode);
        if ($value && is_array($value)) {
            foreach ($value as $key => $duration) {
                if (isset($duration['delete']) && $duration['delete']) {
                    unset($value[$key]);
                    continue;
                }
                if (isset($value[$key]['delete'])) {
                    unset($value[$key]['delete']);
                }
                $value[$key]['price'] = $this->_localeFormat->getNumber(
                    $duration['price']
                );
            }

            $value = $this->_sortValues($value);
            $value = array_values($value);
            $value = json_encode($value);

            $object->setData($attrCode, $value);
        }

        return parent::beforeSave($object);
    }

    /**
     * After load method.
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function afterLoad($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);

        if(!is_array($value)) $value = json_decode($value, true);
        if ($value === null || !is_array($value)) {
            $value = [];
        }

        $object->setData($attrCode, $value);

        return $this;
    }
}
