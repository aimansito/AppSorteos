<?php

namespace App\Entity;

use App\Repository\SorteoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Participante;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SorteoRepository::class)]
class Sorteo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombreActividad = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThanOrEqual("now", message: "La fecha del sorteo debe ser igual o posterior a la fecha actual.")]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(length: 255)]
    private ?string $lugar = null;

    #[ORM\Column]
    private ?int $maxParticipantes = null;

  
    #[ORM\OneToMany(mappedBy: 'sorteo', targetEntity: Participante::class, cascade: ['persist', 'remove'])]
    private Collection $participantes;

    public function __construct()
    {
        $this->participantes = new ArrayCollection();
    }

   
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreActividad(): ?string
    {
        return $this->nombreActividad;
    }

    public function setNombreActividad(string $nombreActividad): static
    {
        $this->nombreActividad = $nombreActividad;
        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;
        return $this;
    }

    public function getLugar(): ?string
    {
        return $this->lugar;
    }

    public function setLugar(string $lugar): static
    {
        $this->lugar = $lugar;
        return $this;
    }

    public function getMaxParticipantes(): ?int
    {
        return $this->maxParticipantes;
    }

    public function setMaxParticipantes(int $maxParticipantes): static
    {
        $this->maxParticipantes = $maxParticipantes;
        return $this;
    }


    /**
     * @return Collection<int, Participante>
     */
    public function getParticipantes(): Collection
    {
        return $this->participantes;
    }

    public function addParticipante(Participante $participante): static
    {
        if (!$this->participantes->contains($participante)) {
            $this->participantes->add($participante);
            $participante->setSorteo($this);
        }

        return $this;
    }

    public function removeParticipante(Participante $participante): static
    {
        if ($this->participantes->removeElement($participante)) {
            if ($participante->getSorteo() === $this) {
                $participante->setSorteo(null);
            }
        }

        return $this;
    }

    public function getPlazasRestantes(): int {
        return $this->maxParticipantes - count($this->participantes);
    }

    public function tienePlazasDisponibles(): bool {
        return $this->getPlazasRestantes() > 0 ; 
    }
}
