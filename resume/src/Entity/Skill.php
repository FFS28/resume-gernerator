<?php

namespace App\Entity;

use App\Enum\SkillTypeEnum;
use App\Helper\StringHelper;
use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[UniqueEntity('slug')]
class Skill implements Stringable
{
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: false)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::STRING, nullable: true, enumType: SkillTypeEnum::class)]
    private SkillTypeEnum $type;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private int $level;

    #[ORM\Column(name: 'on_homepage', type: Types::BOOLEAN, nullable: false)]
    private bool $onHomepage;

    /**
     * @var Collection<Experience>
     */
    #[ORM\ManyToMany(targetEntity: Experience::class, mappedBy: 'skills', cascade: ['persist'])]
    private Collection $experiences;

    #[ORM\ManyToOne(targetEntity: Skill::class, inversedBy: 'children')]
    private ?Skill $parent = null;

    /**
     * @var Collection<Skill>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Skill::class)]
    private Collection $children;

    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'skills')]
    private Collection $projects;

    public function __construct()
    {
        $this->experiences = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->level = 0;
        $this->projects = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
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

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?SkillTypeEnum
    {
        return $this->type;
    }

    public function setType(?SkillTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeName(): string
    {
        return $this->type->toString();
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
     * @return Collection<Experience>
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

    public function getParentName(): ?string
    {
        return $this->parent?->getName();
    }

    /**
     * @return Collection<Skill>
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
     * @param Skill[] $parents
     * @return Skill[]
     */
    public function getAllParents(array $parents = []): array
    {
        if ($this->getParent()) {
            $parent = $this->getParent();

            if (!in_array($parent, $parents)) {
                $parents[] = $parent;

                return $parent->getAllParents($parents);
            }
        }

        return $parents;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addSkill($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            $project->removeSkill($this);
        }

        return $this;
    }
}
