<?php

namespace Vnecoms\VendorsMembership\Block\Vendors\Account\Edit\Form\Renderer;

/**
 * Widget Instance page groups (predefined layouts group) to display on
 *
 * @method \Magento\Widget\Model\Widget\Instance getWidgetInstance()
 */
class ExpiryDate extends \Vnecoms\Vendors\Block\Vendors\Widget\Form\Renderer\Fieldset\Element
{
    protected $_template = 'Vnecoms_VendorsMembership::account/form/renderer/fieldset/expiry_date.phtml';

    /**
     * Notify expiry date
     *
     * @return bool
     */
    public function notifyExpiryDate(){
        $expiryDate = strtotime($this->getElement()->getEscapedValue());
        $today = time();
    
        $differentTime = ($expiryDate - $today)  / (60*60*24); /*Days*/
        return $differentTime <= 7;
    }
}
