<?php

namespace App\Entity;

use App\Repository\ChansonsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChansonsRepository::class)]
class Chansons
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getDisques"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getDisques"])]
    #[Assert\NotBlank(message: "Le titre de la chanson est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le titre doit faire au moins {{ limit }} caractères", maxMessage:"Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $titre = null;

    #[ORM\Column]
    #[Groups(["getDisques"])]
    #[Assert\NotBlank(message: "La durée de la chanson est obligatoire")]
    private ?int $duree = null;

    /**
     * @var Collection<int, Disque>
     */
    #[ORM\ManyToMany(targetEntity: Disque::class, inversedBy: 'chansons')]
    private Collection $disque;

    public function __construct()
    {
        $this->disque = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * @return Collection<int, Disque>
     */
    public function getDisque(): Collection
    {
        return $this->disque;
    }

    public function addDisque(Disque $disque): static
    {
        if (!$this->disque->contains($disque)) {
            $this->disque->add($disque);
        }

        return $this;
    }

    public function removeDisque(Disque $disque): static
    {
        $this->disque->removeElement($disque);

        return $this;
    }
}
