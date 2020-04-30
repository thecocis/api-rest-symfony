<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Valorations
 *
 * @ORM\Table(name="valorations", indexes={@ORM\Index(name="fk_valorationto_user", columns={"user_id"}), @ORM\Index(name="fk_valorationfrom_user", columns={"from_id"})})
 * @ORM\Entity
 */
class Valoration
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
     * @var int
     *
     * @ORM\Column(name="value", type="integer", nullable=false)
     */
    private $value;

    /** 
    * @var \User
    *
    * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="valorations_from")
    * @ORM\JoinColumns({
    *   @ORM\JoinColumn(name="from_id", referencedColumnName="id")
    * })
    */
    private $from;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="valorations_to")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrom(): ?User
    {
        return $this->from;
    }

    public function setFrom(User $from): self
    {
        $this->fromId = $from;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


}
