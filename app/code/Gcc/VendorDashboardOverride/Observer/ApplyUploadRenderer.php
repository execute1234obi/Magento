<?php
namespace Gcc\VendorDashboardOverride\Observer;

use Gcc\VendorDashboardOverride\Block\Vendors\Account\Edit\Form\Renderer\Upload as UploadRenderer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;
use Vnecoms\Vendors\Block\Vendors\Widget\Form\Element\File as VendorFile;
use Vnecoms\Vendors\Block\Vendors\Widget\Form\Element\Image as VendorImage;

class ApplyUploadRenderer implements ObserverInterface
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Apply the upload-card renderer to file/image fields.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $fieldset = $observer->getData('fieldset');
        if (!$fieldset || !method_exists($fieldset, 'getElements')) {
            return;
        }

        $this->applyRendererRecursively($fieldset);
    }

    /**
     * Traverse the form tree and replace file/image renderers.
     *
     * @param mixed $container
     * @return void
     */
    private function applyRendererRecursively($container)
    {
        foreach ($container->getElements() as $element) {
            if ($element instanceof VendorFile || $element instanceof VendorImage) {
                $element->setRenderer($this->layout->createBlock(UploadRenderer::class));
                continue;
            }

            if (method_exists($element, 'getElements')) {
                $this->applyRendererRecursively($element);
            }
        }
    }
}
