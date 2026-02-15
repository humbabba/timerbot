<?php

namespace App\Traits;

trait Copyable
{
    /**
     * Create a duplicate of this model instance.
     * The name field will have " (copy)" appended.
     *
     * @return static
     */
    public function duplicate(): static
    {
        $copy = $this->replicate();

        // Append " (copy)" to the name
        if (isset($copy->name)) {
            $copy->name = $this->name . ' (copy)';
        }

        $copy->save();

        return $copy;
    }
}
