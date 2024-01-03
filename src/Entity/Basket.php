<?php

namespace App\Entity;

use App\Repository\BasketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasketRepository::class)]
class Basket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'basket', targetEntity: Order::class)]
    private Collection $orders;

    #[ORM\OneToOne(inversedBy: 'basket', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Product::class)]
    private Collection $basket_products;

    #[ORM\Column]
    private array $productQuantities = [];

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->basket_products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setBasket($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getBasket() === $this) {
                $order->setBasket(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getBasketProducts(): Collection
    {
        return $this->basket_products;
    }

    public function addBasketProduct(Product $basketProduct): static
    {
        if (!$this->basket_products->contains($basketProduct)) {
            $this->basket_products->add($basketProduct);
        }

        return $this;
    }

    public function removeBasketProduct(Product $basketProduct): static
    {
        $this->basket_products->removeElement($basketProduct);

        return $this;
    }

    public function getProductQuantities(): array
    {
        return $this->productQuantities;
    }

    public function setProductQuantities(Product $product, int $quantity): static
    {
        $productId = $product->getId();
        $this->productQuantities[$productId] = $quantity;

        return $this;
    }

    public function updateProductQuantity(Product $product, int $newQuantity): void
    {
        $this->productQuantities = [
            $product->getId() => $newQuantity];
    }
}
