<?php

namespace App\Entity;

use App\Helper\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 * @UniqueEntity("slug")
 */
class Company
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $displayName;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Experience", mappedBy="company", cascade={"persist"})
     */
    private $experiences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice", mappedBy="company", cascade={"persist"})
     */
    private $invoices;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    const TYPE_CLIENT    = "client";
    const TYPE_PROSPECT = "prospect";
    const TYPE_ARCHIVE = "archive";
    const TYPE_ESN = "esn";
    const TYPE_COMPANY = "company";

    /** @var array user friendly named type */
    const TYPES = [
        'Client' => self::TYPE_CLIENT,
        'Prospect' => self::TYPE_PROSPECT,
        'Archive' => self::TYPE_ARCHIVE,
        'ESN' => self::TYPE_ESN,
        'Company' => self::TYPE_COMPANY,
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Person", mappedBy="company", cascade={"persist"})
     */
    private $persons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="company")
     */
    private $activities;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="clients")
     */
    private $contractor;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Company", mappedBy="contractor")
     */
    private $clients;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tjm;

    public function __construct()
    {
        $this->experiences = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->persons = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->setSlug(StringHelper::slugify($name));

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        $typeName = array_flip(self::TYPES);
        if (!isset($typeName[$this->type])) {
            return null;
        }

        return $typeName[$this->type];
    }

    /**
     * @return Collection|Experience[]
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    /**
     * @return Experience
     */
    public function getLastExperience()
    {
        $count = count($this->getExperiences());
        return $count > 1 ? $this->experiences[$count - 1] : null;
    }

    public function addExperience(Experience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->setCompany($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): self
    {
        if ($this->experiences->contains($experience)) {
            $this->experiences->removeElement($experience);
            // set the owning side to null (unless already changed)
            if ($experience->getCompany() === $this) {
                $experience->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @param Company[] $contractors
     * @return Company[]
     */
    public function getAllContractors($contractors = []): array
    {
        if ($this->getContractor()) {
            $contractor = $this->getContractor();

            if (!in_array($contractor, $contractors)) {
                $contractors[] = $contractor;

                return $contractor->getAllContractors($contractors);
            }
        }

        return $contractors;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setCompany($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getCompany() === $this) {
                $invoice->setCompany(null);
            }
        }

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName ? $this->displayName : $this->getName();
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection|Person[]
     */
    public function getPersons(): Collection
    {
        return $this->persons;
    }

    public function getEmail(): string
    {
        $email = '';
        foreach ($this->getPersons() as $person)
        {
            if ($person->getIsInvoicingDefault()) {
                return $person->getEmail();
            }
            elseif (!$email) {
                $email = $person->getEmail();
            }
        }
        return $email;
    }

    public function addPerson(Person $person): self
    {
        if (!$this->persons->contains($person)) {
            $this->persons[] = $person;
            $person->setCompany($this);
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->persons->contains($person)) {
            $this->persons->removeElement($person);
            // set the owning side to null (unless already changed)
            if ($person->getCompany() === $this) {
                $person->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Activity[]
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->activities->contains($activity)) {
            $this->activities[] = $activity;
            $activity->setInvoice($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->contains($activity)) {
            $this->activities->removeElement($activity);
            // set the owning side to null (unless already changed)
            if ($activity->getInvoice() === $this) {
                $activity->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Company|null
     */
    public function getContractor()
    {
        return $this->contractor;
    }

    public function getContractorName()
    {
        return $this->contractor ? $this->contractor : '';
    }

    public function setContractor(?self $contractor): self
    {
        $this->contractor = $contractor;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    /**
     * @return Company
     */
    public function getClient()
    {
        return count($this->clients) == 1 ? $this->clients[0] : '';
    }

    public function addClient(self $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setContractor($this);
        }

        return $this;
    }

    public function removeClient(self $client): self
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
            // set the owning side to null (unless already changed)
            if (!$client->getContractor()) {
                $client->setContractor(null);
            }
        }

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getTjm(): ?int
    {
        return $this->tjm;
    }

    public function setTjm(?int $tjm): self
    {
        $this->tjm = $tjm;

        return $this;
    }
}
