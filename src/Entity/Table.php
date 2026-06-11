<?php

namespace App\Entity;

use App\Repository\TableRepository;
use App\ApiResource\TableGuestsController;
use App\ApiResource\TableStatsController;
use Symfony\Component\Serializer\Annotation\MaxDepth;
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
    normalizationContext: [
        'groups' => ['tables:read'],
        'enable_max_depth' => true
    ],
    denormalizationContext: ['groups' => ['tables:write']],
    paginationEnabled: false,
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),

        new Get(
            uriTemplate: '/tables/{id}/guests',
            controller: TableGuestsController::class,
            read: false
        ),

        new GetCollection(
            uriTemplate: '/tablesStats',
            controller: TableStatsController::class,
            read: false,
            paginationEnabled: false
        ),
    ]
)]

#[ORM\Entity(repositoryClass: TableRepository::class)]
#[ORM\Table(name: 'guest_table')]
#[UniqueEntity(fields: ['numTable'], message: 'Стол с таким номером уже существует')]
class Table
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tables:read', 'listguests:read'])]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    #[Groups(['tables:read', 'tables:write', 'listguests:read'])]
    private ?int $numTable = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tables:read', 'tables:write', 'listguests:read'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['tables:read', 'tables:write', 'listguests:read'])]
    private ?int $maxGuests = null;

    /**
     * @var Collection<int, ListGuest>
     */
    #[ORM\OneToMany(targetEntity: ListGuest::class, mappedBy: 'table')]
    #[Groups(['tables:read', 'tables:write'])]
    #[MaxDepth(1)]
    private Collection $listGuests;

    public function __construct()
    {
        $this->listGuests = new ArrayCollection();
    }

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
            $listGuest->setTable($this);
        }

        return $this;
    }

    public function removeListGuest(ListGuest $listGuest): static
    {
        if ($this->listGuests->removeElement($listGuest)) {
            // set the owning side to null (unless already changed)
            if ($listGuest->getTable() === $this) {
                $listGuest->setTable(null);
            }
        }

        return $this;
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

    #[Groups(['tables:read', 'listguests:read'])]
    public function getGuests(): int
    {
        return $this->listGuests->count();
    }

    #[Groups(['tables:read', 'listguests:read'])]
    public function getPresentGuests(): int
    {
        return $this->listGuests
            ->filter(fn (ListGuest $guest) => $guest->getIsPresent())
            ->count();
    }

    public function __toString(): string
    {
        return 'Стол: ' . $this->numTable;
    }
}
