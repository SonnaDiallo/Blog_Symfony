<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class SlugService
{
    public function __construct(
        private SluggerInterface $slugger
    ) {
    }

    public function generateUniqueSlug(string $title, ?callable $existsCallback = null): string
    {
        $slug = strtolower($this->slugger->slug($title)->toString());
        
        if ($existsCallback === null) {
            return $slug;
        }

        $originalSlug = $slug;
        $counter = 1;

        while ($existsCallback($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
