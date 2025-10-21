<?php

namespace App\Entity;

use App\Repository\SorteoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Participante;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
    #[Assert\NotBlank(message: 'La fecha no puede estar vacía.')]
    #[Assert\GreaterThan(
        'now',
        message: 'La fecha debe ser posterior al momento actual.'
    )]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(length: 255)]
    private ?string $lugar = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxParticipantes = null;
    
    #[ORM\Column]
    private bool $participantesIlimitados = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagen = null;

    #[ORM\Column]
    private bool $activo = true;

    #[ORM\Column]
    private int $numeroGanadores = 1;

    #[ORM\OneToMany(mappedBy: 'sorteo', targetEntity: Participante::class, cascade: ['persist', 'remove'])]
    private Collection $participantes;

    public function __construct()
    {
        $this->participantes = new ArrayCollection();
        $this->numeroGanadores = 1;
    }

    // NUEVA VALIDACIÓN CON TIMEZONE CORRECTO
    #[Assert\Callback]
    public function validateFecha(ExecutionContextInterface $context): void
    {
        if ($this->fecha === null) {
            return;
        }
        
        // Crear fecha actual en timezone de Madrid
        $ahora = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Madrid'));
        
        // Convertir la fecha del sorteo a DateTimeImmutable si es necesario
        $fechaSorteo = $this->fecha instanceof \DateTimeImmutable 
            ? $this->fecha 
            : \DateTimeImmutable::createFromMutable($this->fecha);
        
        // Comparar usando timestamps
        if ($fechaSorteo->getTimestamp() < $ahora->getTimestamp()) {
            $context->buildViolation('La fecha y hora del sorteo deben ser iguales o posteriores al momento actual.')
                ->atPath('fecha')
                ->addViolation();
        }
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

    public function setMaxParticipantes(?int $maxParticipantes): static
    {
        $this->maxParticipantes = $maxParticipantes;
        return $this;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(?string $imagen): static
    {
        $this->imagen = $imagen;
        return $this;
    }
    
    public function isParticipantesIlimitados(): bool
    {
        return $this->participantesIlimitados;
    }
    
    public function setParticipantesIlimitados(bool $participantesIlimitados): static
    {
        $this->participantesIlimitados = $participantesIlimitados;
        return $this;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;
        return $this;
    }

    public function getNumeroGanadores(): int
    {
        return $this->numeroGanadores;
    }

    public function setNumeroGanadores(int $numeroGanadores): static
    {
        $this->numeroGanadores = max(1, $numeroGanadores);
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
        if ($this->participantesIlimitados) {
            return PHP_INT_MAX;
        }
        return $this->maxParticipantes - count($this->participantes);
    }

    public function tienePlazasDisponibles(): bool {
        if ($this->participantesIlimitados) {
            return true;
        }
        return $this->getPlazasRestantes() > 0; 
    }
}
