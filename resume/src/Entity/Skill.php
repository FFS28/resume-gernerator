<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Skill
 *
 * @ORM\Table(name="skill")
 * @ORM\Entity
 */
class Skill
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    const TYPE_SOFTWARE    = "software";
    const TYPE_FRAMEWORK = "framework";
    const TYPE_PLATFORM = "platform";
    const TYPE_LANGUAGE  = "language";
    const TYPE_OS  = "os";

    /** @var array user friendly named type */
    protected static $typeName = [
        self::TYPE_SOFTWARE => 'Software',
        self::TYPE_FRAMEWORK => 'Framework',
        self::TYPE_PLATFORM  => 'Platform',
        self::TYPE_LANGUAGE    => 'Language',
        self::TYPE_OS  => 'OS',
    ];

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        if (!isset(static::$typeName[$this->type])) {
            return null;
        }

        return static::$typeName[$this->type];
    }

    /**
     * @return array<string>
     */
    public static function getAvailableTypes()
    {
        return array_keys(static::$typeName);
    }

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="string")
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private $level;

    /**
     * @var bool
     *
     * @ORM\Column(name="on_homepage", type="boolean", nullable=false)
     */
    private $onHomepage;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Experience", mappedBy="skills")
     */
    private $experiences;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Skill", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Skill", mappedBy="parent")
     */
    private $children;

    public function __construct()
    {
        $this->experiences = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getOnHomepage(): ?bool
    {
        return $this->onHomepage;
    }

    public function setOnHomepage(bool $onHomepage): self
    {
        $this->onHomepage = $onHomepage;

        return $this;
    }

    /**
     * @return Collection|Experience[]
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(Experience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->addSkill($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): self
    {
        if ($this->experiences->contains($experience)) {
            $this->experiences->removeElement($experience);
            $experience->removeSkill($this);
        }

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|Skill[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Skill $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Skill $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }
}
