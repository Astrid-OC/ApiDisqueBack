<?php

namespace App\Entity;

use App\Repository\DisqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
* @Hateoas\Relation(
* "self",
* href = @Hateoas\Route(
* "detailDisque",
* parameters = { "id" = "expr(object.getId())" }
* ),
* exclusion = @Hateoas\Exclusion(groups="getDisques")
* )
*
* @Hateoas\Relation(
* "delete",
* href = @Hateoas\Route(
* "deleteDisque",
* parameters = { "id" = "expr(object.getId())" },
* ),
* exclusion = @Hateoas\Exclusion(groups="getDisques", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
* )
*
* @Hateoas\Relation(
* "update",
* href = @Hateoas\Route(
* "updateDisque",
* parameters = { "id" = "expr(object.getId())" },
* ),
* exclusion = @Hateoas\Exclusion(groups="getDisques", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
* )
*/
#[ORM\Entity(repositoryClass: DisqueRepository::class)]
class Disque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getDisques"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getDisques"])]
    #[Assert\NotBlank(message: "Le nom du disque est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage:"Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $NomDisque = null;
    //relation effectuée entre disque et chanteur.
    #[ORM\ManyToOne(inversedBy: 'disques')]
    //Permet la suppression en cascade. Les disques seront supprimés en même temps que leur chanteurs quand on voudra supprimer ce dernier.
    #[ORM\JoinColumn(onDelete:"CASCADE")]
    #[Groups(["getDisques"])]
    private ?Chanteur $chanteur = null;

    /**
     * @var Collection<int, Chansons>
     */
    #[ORM\ManyToMany(targetEntity: Chansons::class, mappedBy: 'disque')]
    //relation effectuée entre disque et chansons.
    #[Groups(["getDisques"])]
    private Collection $chansons;

    public function __construct()
    {
        $this->chansons = new ArrayCollection();
    }

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

    public function getChanteur(): ?Chanteur
    {
        return $this->chanteur;
    }

    public function setChanteur(?Chanteur $chanteur): static
    {
        $this->chanteur = $chanteur;

        return $this;
    }

    /**
     * @return Collection<int, Chansons>
     */
    public function getChansons(): Collection
    {
        return $this->chansons;
    }

    public function addChanson(Chansons $chanson): static
    {
        if (!$this->chansons->contains($chanson)) {
            $this->chansons->add($chanson);
            $chanson->addDisque($this);
        }

        return $this;
    }

    public function removeChanson(Chansons $chanson): static
    {
        if ($this->chansons->removeElement($chanson)) {
            $chanson->removeDisque($this);
        }

        return $this;
    }
}
