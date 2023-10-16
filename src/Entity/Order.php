<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $is_pending = null;

    #[ORM\Column]
    private ?bool $is_served = null;

    #[ORM\Column]
    private ?bool $is_notServer = null;

    #[ORM\Column(length: 50)]
    private ?string $customerName = null;

    #[ORM\Column(length: 50)]
    private ?string $customerFirstName = null;

    #[ORM\Column(length: 50)]
    private ?string $customerAdress = null;

    #[ORM\Column(length: 50)]
    private ?string $customerTown = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $fullPrice = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOrder = null;

    #[ORM\OneToOne(inversedBy: 'commande')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Invoice $invoice = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Reference::class)]
    private Collection $archives;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Basket $basket = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->archives = new ArrayCollection();
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

    public function getCustomerAdress(): ?string
    {
        return $this->customerAdress;
    }

    public function setCustomerAdress(string $customerAdress): static
    {
        $this->customerAdress = $customerAdress;

        return $this;
    }

    public function getCustomerTown(): ?string
    {
        return $this->customerTown;
    }

    public function setCustomerTown(string $customerTown): static
    {
        $this->customerTown = $customerTown;

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

    public function getBasket(): ?Basket
    {
        return $this->basket;
    }

    public function setBasket(?Basket $basket): static
    {
        $this->basket = $basket;

        return $this;
    }
}
