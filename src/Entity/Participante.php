<?php

namespace App\Entity;

use App\Repository\ParticipanteRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\Sorteo;

#[ORM\Entity(repositoryClass: ParticipanteRepository::class)]
#[UniqueEntity(
    fields: ['sorteo', 'email'],
    message: 'Ya estás apuntado a este sorteo con este correo electrónico.'
)]
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

    #[ORM\Column(nullable: true)]
    private ?int $puesto = null;

    #[ORM\ManyToOne(targetEntity: Sorteo::class, inversedBy: 'participantes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sorteo $sorteo = null;

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

    public function getPuesto(): ?int
    {
        return $this->puesto;
    }

    public function setPuesto(?int $puesto): static
    {
        $this->puesto = $puesto;
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

    public function setIniciales(): string {
        $nombre = $this->getNombre();
        $palabras = explode(' ', $nombre);
        $iniciales = '';

        foreach($palabras as $palabra) {
            if(!empty($palabra)) {
                $iniciales .= substr($palabra, 0, 1) . '.';
            }
        }

        return strtoupper($iniciales);
    }
}
