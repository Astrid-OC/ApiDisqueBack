<?php

namespace App\Entity;

use App\Repository\DisqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisqueRepository::class)]
class Disque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $NomDisque = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomDisque(): ?string
    {
        return $this->NomDisque;
    }

    public function setNomDisque(string $NomDisque): static
    {
        $this->NomDisque = $NomDisque;

        return $this;
    }
}
