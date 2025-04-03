<?php

namespace Kpopia\SortByOld\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class SortByOldFirst
{
    public function aroundSetOrder($subject, $proceed, $attribute, $direction = 'ASC')
    {
        if ($attribute == 'old_first') {
            return $proceed('created_at', 'ASC'); // Sort by oldest first
        } elseif ($attribute == 'newest_first') {
            return $proceed('created_at', 'DESC'); // Sort by newest first
        }
        
        // Allow Magento's default price sorting behavior (both ASC and DESC)
        if ($attribute == 'price') {
            return $proceed($attribute, $direction);
        }

        return $proceed($attribute, $direction); // Default behavior for other attributes
    }
}
