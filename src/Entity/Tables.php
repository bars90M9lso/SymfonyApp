<?php

namespace App\Entity;

use App\Repository\TablesRepository;
use App\ApiResource\TableGuestsController;
use App\ApiResource\TableStatsController;


use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

// Api config
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ApiFilter(SearchFilter::class, properties: ['numTable' => 'partial'])]
#[ApiResource(
    normalizationContext: ['groups' => ['tables:read']],
    denormalizationContext: ['groups' => ['tables:write']],
    paginationEnabled: false,
    security: "is_granted('ROLE_API')", 
    securityMessage: "API key required",
    
    operations: [
        new GetCollection(security: "is_granted('ROLE_API')"),
        new Get(security: "is_granted('ROLE_API')"),
        new Post(security: "is_granted('ROLE_API')"),
        new Patch(security: "is_granted('ROLE_API')"),
        new Delete(security: "is_granted('ROLE_API')"),
        new Get(
            uriTemplate: '/tables/{id}/guests',
            controller: TableGuestsController::class,
            read: false,
            security: "is_granted('ROLE_API')"
        ),
        new GetCollection(
            uriTemplate: '/tablesStats',
            controller: TableStatsController::class,
            read: false,
            paginationEnabled: false,
            security: "is_granted('ROLE_API')"
        ),
    ]
)]

#[ORM\Entity(repositoryClass: TablesRepository::class)]
#[UniqueEntity(fields: ['numTable'], message: 'Стол с таким номером уже существует')]
class Tables
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tables:read'])]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    #[Groups(['tables:read', 'tables:write'])]
    private ?int $numTable = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tables:read', 'tables:write'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['tables:read', 'tables:write'])]
    private ?int $maxGuests = null;

    /**
     * @var Collection<int, ListGuest>
     */
    #[ORM\OneToMany(targetEntity: ListGuest::class, mappedBy: 'tables')]
    #[Groups(['tables:read', 'tables:write'])]
    private Collection $listGuests;

    /**
     * @return Collection<int, ListGuest>
     */
    public function getListGuests(): Collection
    {
        return $this->listGuests;
    }

    public function addListGuest(ListGuest $listGuest): static
    {
        if (!$this->listGuests->contains($listGuest)) {
            $this->listGuests->add($listGuest);
            $listGuest->setTables($this);
        }

        return $this;
    }

    public function removeListGuest(ListGuest $listGuest): static
    {
        if ($this->listGuests->removeElement($listGuest)) {
            // set the owning side to null (unless already changed)
            if ($listGuest->getTables() === $this) {
                $listGuest->setTables(null);
            }
        }

        return $this;
    }

    public function __construct()
    {
        $this->listGuests = new ArrayCollection();
    }

    public function __toString(): string 
    { 
        return 'Стол: ' . $this->numTable; 
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumTable(): ?int
    {
        return $this->numTable;
    }

    public function setNumTable(int $numTable): static
    {
        $this->numTable = $numTable;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getMaxGuests(): ?int
    {
        return $this->maxGuests;
    }

    public function setMaxGuests(?int $maxGuests): static
    {
        $this->maxGuests = $maxGuests;

        return $this;
    }
    
    #[Groups(['tables:read'])]
    public function getGuests(): int
    {
        return $this->listGuests->count();
    }
    
    #[Groups(['tables:read'])]
    public function getPresentGuests(): int
    {
        return $this->listGuests
            ->filter(fn(ListGuest $guest) => $guest->isPresent())
            ->count();
    }
}
