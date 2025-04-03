<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    const DEFAULT_SORTING_SECTION = 'default_sorting';

    const DEFAULT_SORTING_SEARCH_PAGES_1 = 'search_1';
    const DEFAULT_SORTING_SEARCH_PAGES_2 = 'search_2';
    const DEFAULT_SORTING_SEARCH_PAGES_3 = 'search_3';

    const DEFAULT_SORTING_CATEGORY_PAGES_1 = 'category_1';
    const DEFAULT_SORTING_CATEGORY_PAGES_2 = 'category_2';
    const DEFAULT_SORTING_CATEGORY_PAGES_3 = 'category_3';

    /**
     * @var string
     */
    protected $pathPrefix = 'amsorting/';

    /**
     * @return array
     */
    public function getDefaultSortingSearchPages(): array
    {
        $paths = [
            self::DEFAULT_SORTING_SEARCH_PAGES_1,
            self::DEFAULT_SORTING_SEARCH_PAGES_2,
            self::DEFAULT_SORTING_SEARCH_PAGES_3
        ];

        return $this->getDefaultOrders($paths);
    }

    /**
     * @return array
     */
    public function getDefaultSortingCategoryPages(): array
    {
        $paths = [
            self::DEFAULT_SORTING_CATEGORY_PAGES_1,
            self::DEFAULT_SORTING_CATEGORY_PAGES_2,
            self::DEFAULT_SORTING_CATEGORY_PAGES_3
        ];

        return $this->getDefaultOrders($paths);
    }

    private function getDefaultOrders(array $paths): array
    {
        $defaultOrders = [];
        foreach ($paths as $path) {
            $orderCode = $this->getValue(sprintf('%s/%s', self::DEFAULT_SORTING_SECTION, $path));
            if ($orderCode) {
                $defaultOrders[] = $orderCode;
            }
        }

        return $defaultOrders;
    }
}
