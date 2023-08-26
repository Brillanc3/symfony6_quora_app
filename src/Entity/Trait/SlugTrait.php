<?php

use Doctrine\ORM\Mapping as ORM;

trait SlugTrait
{
    #[ORM\Column(type:'string', length: 255)]
    private ?string $slug = null;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}