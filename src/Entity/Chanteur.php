<?php

namespace App\Entity;

use App\Repository\ChanteurRepository;
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
* "detailChanteur",
* parameters = { "id" = "expr(object.getId())" }
* ),
* exclusion = @Hateoas\Exclusion(groups="getDisques")
* )
*
* @Hateoas\Relation(
* "delete",
* href = @Hateoas\Route(
* "deleteChanteur",
* parameters = { "id" = "expr(object.getId())" },
* ),
* exclusion = @Hateoas\Exclusion(groups="getDisques", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
* )
*
* @Hateoas\Relation(
* "update",
* href = @Hateoas\Route(
* "updateChanteur",
* parameters = { "id" = "expr(object.getId())" },
* ),
* exclusion = @Hateoas\Exclusion(groups="getDisques", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
* )
*/
#[ORM\Entity(repositoryClass: ChanteurRepository::class)]
class Chanteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getDisques"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getDisques"])]
    #[Assert\NotBlank(message: "Le nom du chanteur est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage:"Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $nomChanteur = null;

    /**
     * @var Collection<int, Disque>
     */
    #[ORM\OneToMany(targetEntity: Disque::class, mappedBy: 'chanteur')]
    //relation effectuée entre chanteur et disque.
    private Collection $disques;

    public function __construct()
    {
        $this->disques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomChanteur(): ?string
    {
        return $this->nomChanteur;
    }

    public function setNomChanteur(string $nomChanteur): static
    {
        $this->nomChanteur = $nomChanteur;

        return $this;
    }

    /**
     * @return Collection<int, Disque>
     */
    public function getDisques(): Collection
    {
        return $this->disques;
    }

    public function addDisque(Disque $disque): static
    {
        if (!$this->disques->contains($disque)) {
            $this->disques->add($disque);
            $disque->setChanteur($this);
        }

        return $this;
    }

    public function removeDisque(Disque $disque): static
    {
        if ($this->disques->removeElement($disque)) {
            // set the owning side to null (unless already changed)
            if ($disque->getChanteur() === $this) {
                $disque->setChanteur(null);
            }
        }

        return $this;
    }
}
