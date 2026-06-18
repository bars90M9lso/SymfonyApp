<?php

namespace App\Entity;

use App\Repository\ListGuestRepository;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\TableCapacity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(BooleanFilter::class, properties: ['isPresent'])]
#[ApiResource(
    normalizationContext: ['groups' => ['listguests:read']],
    denormalizationContext: ['groups' => ['listguests:write']],
    paginationEnabled: false
)]
#[TableCapacity]
#[ORM\Entity(repositoryClass: ListGuestRepository::class)]
class ListGuest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['listguests:read', 'listguests:write', 'tables:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['listguests:read', 'listguests:write', 'tables:read'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['listguests:read', 'listguests:write', 'tables:read'])]
    private ?bool $isPresent = null;

    #[ORM\ManyToOne(inversedBy: 'listGuests')]
    #[Groups(['listguests:read', 'listguests:write'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[MaxDepth(1)]
    private ?Table $table = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIsPresent(): ?bool
    {
        return $this->isPresent;
    }

    public function setIsPresent(?bool $isPresent): static
    {
        $this->isPresent = $isPresent;

        return $this;
    }

    public function getTable(): ?Table
    {
        return $this->table;
    }

    public function setTable(?Table $table): static
    {
        $this->table = $table;

        return $this;
    }
}
