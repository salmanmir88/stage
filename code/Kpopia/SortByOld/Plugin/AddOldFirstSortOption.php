<?php

namespace Kpopia\SortByOld\Plugin;

class AddOldFirstSortOption
{
    public function afterGetAttributeUsedForSortByArray($subject, $options)
    {
        unset($options['created_at']);
        $options['old_first'] = __('Old First');
        $options['newest_first'] = __('New First');
        return $options;
    }
}