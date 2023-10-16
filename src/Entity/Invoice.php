<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private ?Order $commande = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Reference::class)]
    private Collection $archives;

    public function __construct()
    {
        $this->archives = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Order
    {
        return $this->commande;
    }

    public function setCommande(Order $commande): static
    {
        // set the owning side of the relation if necessary
        if ($commande->getInvoice() !== $this) {
            $commande->setInvoice($this);
        }

        $this->commande = $commande;

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
            $archive->setInvoice($this);
        }

        return $this;
    }

    public function removeArchive(Reference $archive): static
    {
        if ($this->archives->removeElement($archive)) {
            // set the owning side to null (unless already changed)
            if ($archive->getInvoice() === $this) {
                $archive->setInvoice(null);
            }
        }

        return $this;
    }
}
