<?php

namespace App\Entity;

use App\Repository\HistoricoRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Participante;
use App\Entity\Sorteo;

/**
 * Entidad Histórico
 * Registra los resultados de un sorteo: ganador/es y puesto.
 * Campos:
 * - nombreActividad, fecha: referencia temporal
 * - ganador: Participante vencedor
 * - sorteo: referencia al sorteo original
 * - puesto: posición del ganador cuando hay varios
 */
#[ORM\Entity(repositoryClass: HistoricoRepository::class)]
class Historico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombreActividad = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\ManyToOne(targetEntity: Participante::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participante $ganador = null;

    #[ORM\ManyToOne(targetEntity: Sorteo::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sorteo $sorteo = null;

    #[ORM\Column(nullable: true)]
    private ?int $puesto = null;

    // Getters y setters

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

    public function getGanador(): ?Participante
    {
        return $this->ganador;
    }

    public function setGanador(?Participante $ganador): static
    {
        $this->ganador = $ganador;
        return $this;
    }

    public function getSorteo(): ?Sorteo
    {
        return $this->sorteo;
    }

    public function setSorteo(?Sorteo $sorteo): static
    {
        $this->sorteo = $sorteo;
        return $this;
    }

    public function getPuesto(): ?int
    {
        return $this->puesto;
    }

    public function setPuesto(?int $puesto): static
    {
        $this->puesto = $puesto;
        return $this;
    }
}
