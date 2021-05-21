<?php

namespace App\Entity\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateHousehold
{

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(min:2, max:255)]
    #[Assert\Regex('/^[a-z\@][a-z\-\s\@\.]*[a-z\@]$/i')]
    private $title;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return UpdateHousehold
     */
    public function setTitle(string $title): UpdateHousehold
    {
        $this->title = $title;
        return $this;
    }
}
