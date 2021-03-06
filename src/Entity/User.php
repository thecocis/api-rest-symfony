<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class User implements \jsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="surname", type="string", length=150, nullable=true)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="entity", type="string", length=150, nullable=true)
     */
    private $entity;

    /**
     * @var string|null
     *
     * @ORM\Column(name="charge", type="string", length=150, nullable=true)
     */
    private $charge;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string|null
     *
     * @ORM\Column(name="biography", type="text", length=65535, nullable=true)
     */
    private $biography;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var float|null
     *
     * @ORM\Column(name="valoration", type="float", nullable=true)
     */
    private $valoration;

    /**
     * @var int
     *
     * @ORM\Column(name="prefix", type="integer", nullable=false)
     */
    private $prefix;

    /**
     * @var int
     *
     * @ORM\Column(name="telephone", type="integer", nullable=false)
     */
    private $telephone;

    /**
     * @var int
     *
     * @ORM\Column(name="num_valoration", type="integer", nullable=false)
     */
    private $numValoration;

    /**
     * @var string|null
     *
     * @ORM\Column(name="role", type="string", length=20, nullable=true)
     */
    private $role;


    /**
     * @ORM\OneToMany (targetEntity="App\Entity\Event", mappedBy="user")
     */
    private $events;

    /**
     * @ORM\OneToMany (targetEntity="App\Entity\Valoration", mappedBy="user")
     */
    private $valorations_to;

    /**
     * @ORM\OneToMany (targetEntity="App\Entity\Valoration", mappedBy="from")
     */
    private $valorations_from;

    /**
     * @ORM\OneToMany (targetEntity="App\Entity\Comment", mappedBy="user")
     */
    private $comments_to;

    /**
     * @ORM\OneToMany (targetEntity="App\Entity\Comment", mappedBy="from")
     */
    private $comments_from;

    /**
     * @ORM\OneToMany (targetEntity="App\Entity\Participant", mappedBy="user")
     */
    private $participants;


    public function __construct() {
        $this->events = new ArrayCollection();
        $this->valorations = new ArrayCollection();
        $this->comments_from = new ArrayCollection();
        $this->comments_to = new ArrayCollection();
        $this->valorations_to = new ArrayCollection();
        $this->valorations_from = new ArrayCollection();
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(?string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getCharge(): ?string
    {
        return $this->charge;
    }

    public function setCharge(?string $charge): self
    {
        $this->charge = $charge;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): self
    {
        $this->biography = $biography;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getValoration(): ?float
    {
        return $this->valoration;
    }

    public function setValoration(?float $valoration): self
    {
        $this->valoration = $valoration;

        return $this;
    }

    public function getPrefix(): ?int
    {
        return $this->prefix;
    }

    public function setPrefix(int $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    public function setTelephone(int $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getNumValoration(): ?int
    {
        return $this->numValoration;
    }

    public function setNumValoration(int $numValoration): self
    {
        $this->numValoration = $numValoration;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection{
        return $this->events;
    }

    /**
     * @return Collection|Valoration[]
     */
    public function getValorationsFrom(): Collection{
        return $this->valorations_from;
    }

    /**
     * @return Collection|Valoration[]
     */
    public function getValorationsTo(): Collection{
        return $this->valorations_to;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getCommentsFrom(): Collection{
        return $this->comments_from;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getCommentsTo(): Collection{
        return $this->comments_to;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection{
        return $this->participants;
    }

    public function jsonSerialize(): array{

        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'entity' => $this->entity,
            'charge' => $this->charge,
            'biography' => $this->biography,
            'valoration' => $this->valoration,
            'num_valoration' => $this->numValoration,
            'image' => $this->image,
            'prefix' => $this->prefix,
            'telephone' => $this->telephone,
            'role' => $this->role
        ];
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setUser($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }

        return $this;
    }

    public function addValoration(Valoration $valoration): self
    {
        if (!$this->valorations->contains($valoration)) {
            $this->valorations[] = $valoration;
            $valoration->setUser($this);
        }

        return $this;
    }

    public function removeValoration(Valoration $valoration): self
    {
        if ($this->valorations->contains($valoration)) {
            $this->valorations->removeElement($valoration);
            // set the owning side to null (unless already changed)
            if ($valoration->getUser() === $this) {
                $valoration->setUser(null);
            }
        }

        return $this;
    }

    public function addValorationsTo(Valoration $valorationsTo): self
    {
        if (!$this->valorations_to->contains($valorationsTo)) {
            $this->valorations_to[] = $valorationsTo;
            $valorationsTo->setUser($this);
        }

        return $this;
    }

    public function removeValorationsTo(Valoration $valorationsTo): self
    {
        if ($this->valorations_to->contains($valorationsTo)) {
            $this->valorations_to->removeElement($valorationsTo);
            // set the owning side to null (unless already changed)
            if ($valorationsTo->getUser() === $this) {
                $valorationsTo->setUser(null);
            }
        }

        return $this;
    }

    public function addValorationsFrom(Valoration $valorationsFrom): self
    {
        if (!$this->valorations_from->contains($valorationsFrom)) {
            $this->valorations_from[] = $valorationsFrom;
            $valorationsFrom->setFrom($this);
        }

        return $this;
    }

    public function removeValorationsFrom(Valoration $valorationsFrom): self
    {
        if ($this->valorations_from->contains($valorationsFrom)) {
            $this->valorations_from->removeElement($valorationsFrom);
            // set the owning side to null (unless already changed)
            if ($valorationsFrom->getFrom() === $this) {
                $valorationsFrom->setFrom(null);
            }
        }

        return $this;
    }

    public function addCommentsTo(Comment $commentsTo): self
    {
        if (!$this->comments_to->contains($commentsTo)) {
            $this->comments_to[] = $commentsTo;
            $commentsTo->setUser($this);
        }

        return $this;
    }

    public function removeCommentsTo(Comment $commentsTo): self
    {
        if ($this->comments_to->contains($commentsTo)) {
            $this->comments_to->removeElement($commentsTo);
            // set the owning side to null (unless already changed)
            if ($commentsTo->getUser() === $this) {
                $commentsTo->setUser(null);
            }
        }

        return $this;
    }

    public function addCommentsFrom(Comment $commentsFrom): self
    {
        if (!$this->comments_from->contains($commentsFrom)) {
            $this->comments_from[] = $commentsFrom;
            $commentsFrom->setFrom($this);
        }

        return $this;
    }

    public function removeCommentsFrom(Comment $commentsFrom): self
    {
        if ($this->comments_from->contains($commentsFrom)) {
            $this->comments_from->removeElement($commentsFrom);
            // set the owning side to null (unless already changed)
            if ($commentsFrom->getFrom() === $this) {
                $commentsFrom->setFrom(null);
            }
        }

        return $this;
    }

}
