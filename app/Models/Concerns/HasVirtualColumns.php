<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait HasVirtualColumns
{
    /**
     * @param  array<string, bool>  $options
     */
    public function save(array $options = [])
    {
        // if (property_exists($this, 'virtualColumns')) {
        $this->attributes = array_diff_key($this->attributes, array_flip($this->virtualColumns));
        // }

        $return = parent::save($options);

        return $return;
    }
}
