<?php
namespace Tests\Database;

class Test
{
    private $slug;

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug)
    {
        $this->slug = $slug . "test";
    }
}
