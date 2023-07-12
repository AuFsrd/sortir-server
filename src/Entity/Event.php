<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\EventRepository;
use App\Services\CustomFilterLogic;
use App\Services\EventFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Metaclass\FilterBundle\Filter\FilterLogic;
use Metaclass\FilterBundle\Filter\RemoveFakeLeftJoin;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'event:read']),
        new GetCollection(normalizationContext: ['groups' => 'event:read']),
        new Post(normalizationContext: ['groups' => 'event:write']),
        new Patch(normalizationContext: ['groups' => 'event:write'])
    ],
    paginationEnabled: false
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'iword_start',
    'organiser.site' => 'exact',
    'organiser' => 'exact',
    'participants' => 'exact',
    'status.name' => 'exact',
])]
#[ApiFilter(DateFilter::class, properties: [
    'startDateTime',
    'registrationDeadline'
])]
#[ApiFilter(FilterLogic::class)]
#[ApiFilter(EventFilter::class)]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:255, minMessage: 'Too short')]
    #[Groups(['user:read', 'event:read', 'event:write'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\GreaterThan('today UTC')]
    private ?\DateTimeImmutable $startDateTime = null;

    #[ORM\Column]
    #[Assert\NotBlank()]
    #[Assert\GreaterThan(0)]
    #[Groups(['event:read', 'event:write'])]
    private ?int $duration = null;

    #[ORM\Column]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\GreaterThan('today UTC')]
    private ?\DateTimeImmutable $registrationDeadline = null;

    #[Assert\NotBlank()]
    #[Assert\GreaterThan(1)]
    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['event:read', 'event:write'])]
    private ?int $maxParticipants = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event:read', 'event:write'])] // Pour la publication
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event:read', 'event:write'])]
    private ?Venue $venue = null;

    #[ORM\ManyToOne(inversedBy: 'eventsAsOrganiser')]

    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event:read', 'event:write'])]
    private ?User $organiser = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'eventsAsParticipant')]
    #[Groups(['event:read'])]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

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

    public function getStartDateTime(): ?\DateTimeImmutable
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeImmutable $startDateTime): static
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRegistrationDeadline(): ?\DateTimeImmutable
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(\DateTimeImmutable $registrationDeadline): static
    {
        $this->registrationDeadline = $registrationDeadline;

        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;

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

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): static
    {
        $this->venue = $venue;

        return $this;
    }

    public function getOrganiser(): ?User
    {
        return $this->organiser;
    }

    public function setOrganiser(?User $organiser): static
    {
        $this->organiser = $organiser;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function addParticipants(array $listParticipants): static
    {
        foreach ($listParticipants as $p) {
            $this->participants[]=$p;
        }
        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);
        $participant->removeEventAsParticipant($this);

        return $this;
    }

    public function removeParticipants(Collection $listParticipants): static
    {
        foreach ($listParticipants as $p) {
            $this->removeParticipant($p);
//            $p->removeEventAsParticipant($this);
        }

        return $this;
    }

}
