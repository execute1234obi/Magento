<?php
declare(strict_types=1);

namespace Custom\SearchExtended\Plugin\Catalog;

use Magento\Catalog\Helper\Product\ProductList as ProductListHelper;
use Magento\Framework\App\Request\Http as HttpRequest;

class ProductList
{
    private const SEARCH_LIMITS = [4 => 4, 8 => 8, 12 => 12];
    private const SEARCH_DEFAULT_LIMIT = 4;

    public function __construct(
        private readonly HttpRequest $request
    ) {
    }

    public function afterGetAvailableLimit(
        ProductListHelper $subject,
        array $result,
        string $viewMode
    ): array {
        if ($this->isCatalogSearchPage()) {
            return self::SEARCH_LIMITS;
        }

        return $result;
    }

    public function afterGetDefaultLimitPerPageValue(
        ProductListHelper $subject,
        int $result,
        string $viewMode
    ): int {
        if ($this->isCatalogSearchPage()) {
            return self::SEARCH_DEFAULT_LIMIT;
        }

        return $result;
    }

    private function isCatalogSearchPage(): bool
    {
        return $this->request->getFullActionName() === 'catalogsearch_result_index';
    }
}
