<?php

namespace App\Entity;

use App\Repository\ParticipanteRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Sorteo;

#[ORM\Entity(repositoryClass: ParticipanteRepository::class)]
class Participante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "El correo electrónico es obligatorio.")]
    #[Assert\Email(message: "El correo '{{ value }}' no es válido.")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $codigoEntrada = null;

    #[ORM\Column]
    private ?bool $esGanador = false;

    #[ORM\ManyToOne(targetEntity: Sorteo::class, inversedBy: 'participantes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sorteo $sorteo = null;

    // Getters y setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getCodigoEntrada(): ?string
    {
        return $this->codigoEntrada;
    }

    public function setCodigoEntrada(string $codigoEntrada): static
    {
        $this->codigoEntrada = $codigoEntrada;
        return $this;
    }

    public function isEsGanador(): ?bool
    {
        return $this->esGanador;
    }

    public function setEsGanador(bool $esGanador): static
    {
        $this->esGanador = $esGanador;
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
}
