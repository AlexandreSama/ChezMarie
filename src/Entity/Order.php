<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'orders')]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->products->removeElement($product);

        return $this;
    }
}
