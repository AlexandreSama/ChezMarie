<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource]
class Order
{
    // #[Groups(['read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[Groups(['read', 'write'])]
    #[ORM\Column]
    private ?bool $is_pending = null;

    // #[Groups(['read', 'write'])]
    #[ORM\Column]
    private ?bool $is_served = null;

    // #[Groups(['read', 'write'])]
    #[ORM\Column]
    private ?bool $is_notServer = null;

    // #[Groups(['read'])]
    #[ORM\Column(length: 50)]
    private ?string $customerName = null;

    // #[Groups(['read'])]
    #[ORM\Column(length: 50)]
    private ?string $customerFirstName = null;

    // #[Groups(['read'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $fullPrice = null;

    // #[Groups(['read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOrder = null;

    // #[Groups(['read'])]
    #[ORM\OneToOne(inversedBy: 'commande')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Invoice $invoice = null;

    // #[Groups(['read'])]
    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Reference::class)]
    private Collection $archives;

    // #[Groups(['read'])]
    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userid = null;

    // #[Groups(['read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $desiredPickupDateTime = null;

    // #[Groups(['read', 'write'])]
    #[ORM\Column]
    private ?bool $is_preparing = null;

    // #[Groups(['read'])]
    #[ORM\Column(type: "string", nullable: true)]
    private $stripeToken;

    // #[Groups(['read'])]
    #[ORM\Column(length: 25)]
    private ?string $reference = null;

    #[ORM\Column(length: 50)]
    private ?string $phone = null;

    public function __construct()
    {
        $this->archives = new ArrayCollection();
    }

    public function getStripeToken(): ?string
    {
        return $this->stripeToken;
    }

    public function setStripeToken(?string $stripeToken): self
    {
        $this->stripeToken = $stripeToken;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsPending(): ?bool
    {
        return $this->is_pending;
    }

    public function setIsPending(bool $is_pending): static
    {
        $this->is_pending = $is_pending;

        return $this;
    }

    public function isIsServed(): ?bool
    {
        return $this->is_served;
    }

    public function setIsServed(bool $is_served): static
    {
        $this->is_served = $is_served;

        return $this;
    }

    public function isIsNotServer(): ?bool
    {
        return $this->is_notServer;
    }

    public function setIsNotServer(bool $is_notServer): static
    {
        $this->is_notServer = $is_notServer;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerFirstName(): ?string
    {
        return $this->customerFirstName;
    }

    public function setCustomerFirstName(string $customerFirstName): static
    {
        $this->customerFirstName = $customerFirstName;

        return $this;
    }

    public function getFullPrice(): ?string
    {
        return $this->fullPrice;
    }

    public function setFullPrice(string $fullPrice): static
    {
        $this->fullPrice = $fullPrice;

        return $this;
    }

    public function getDateOrder(): ?\DateTimeInterface
    {
        return $this->dateOrder;
    }

    public function setDateOrder(\DateTimeInterface $dateOrder): static
    {
        $this->dateOrder = $dateOrder;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @return Collection<int, Reference>
     */
    public function getArchives(): Collection
    {
        return $this->archives;
    }

    public function addArchive(Reference $archive): static
    {
        if (!$this->archives->contains($archive)) {
            $this->archives->add($archive);
            $archive->setCommande($this);
        }

        return $this;
    }

    public function removeArchive(Reference $archive): static
    {
        if ($this->archives->removeElement($archive)) {
            // set the owning side to null (unless already changed)
            if ($archive->getCommande() === $this) {
                $archive->setCommande(null);
            }
        }

        return $this;
    }

    public function getUserid(): ?User
    {
        return $this->userid;
    }

    public function setUserid(?User $userid): static
    {
        $this->userid = $userid;

        return $this;
    }

    public function getDesiredPickupDateTime(): ?\DateTimeInterface
    {
        return $this->desiredPickupDateTime;
    }

    public function setDesiredPickupDateTime(\DateTimeInterface $desiredPickupDateTime): static
    {
        $this->desiredPickupDateTime = $desiredPickupDateTime;

        return $this;
    }

    public function isIsPreparing(): ?bool
    {
        return $this->is_preparing;
    }

    public function setIsPreparing(bool $is_preparing): static
    {
        $this->is_preparing = $is_preparing;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
