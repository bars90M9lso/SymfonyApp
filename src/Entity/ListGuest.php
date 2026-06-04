<?php

namespace App\Entity;

use App\Repository\ListGuestRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;
// Api config
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
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

#[ORM\Entity(repositoryClass: ListGuestRepository::class)]
class ListGuest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['listguests:read', 'listguests:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['listguests:read', 'listguests:write'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['listguests:read', 'listguests:write'])]
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
