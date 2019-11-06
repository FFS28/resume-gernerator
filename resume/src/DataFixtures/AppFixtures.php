<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use App\Entity\Company;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Hobby;
use App\Entity\Link;
use App\Entity\Skill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var Skill[]
     */
    private $skills;

    /**
     * @var Company[]
     */
    private $companies;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->loadAttributes();
        $this->loadEducations();
        $this->loadHobbies();
        $this->loadLinks();

        $this->loadSkills();
        $this->loadCompanies();
        $this->loadExperience();

        $this->manager->flush();
    }

    private function loadAttributes()
    {
        $attribute = new Attribute();
        $attribute->setSlug('tjm');
        $attribute->setValue('TJM: 400€');
        $attribute->setWeight(90);
        $this->manager->persist($attribute);

        $attribute = new Attribute();
        $attribute->setSlug('quote');
        $attribute->setValue('Programming is a creative art form based in logic. Every programmer is different');
        $attribute->setWeight(0);
        $this->manager->persist($attribute);

        $attribute = new Attribute();
        $attribute->setSlug('name');
        $attribute->setValue('Jérémy ACHAIN');
        $attribute->setWeight(0);
        $this->manager->persist($attribute);

        $attribute = new Attribute();
        $attribute->setSlug('job');
        $attribute->setValue('Développeur Web');
        $attribute->setWeight(0);
        $this->manager->persist($attribute);

        $attribute = new Attribute();
        $attribute->setSlug('status');
        $attribute->setValue('Freelance');
        $attribute->setWeight(100);
        $this->manager->persist($attribute);

        $attribute = new Attribute();
        $attribute->setSlug('location');
        $attribute->setValue('Lyon, France');
        $attribute->setWeight(0);
        $this->manager->persist($attribute);

        $attribute = new Attribute();
        $attribute->setSlug('subtitle');
        $attribute->setValue('Développeur Web Freelance spécialisé Symfony et Angular');
        $attribute->setWeight(0);
        $this->manager->persist($attribute);

        $attribute = new Attribute();
        $attribute->setSlug('duration');
        $attribute->setValue('3 mois renouvelables ou moins');
        $attribute->setWeight(0);
        $this->manager->persist($attribute);

        $attribute = new Attribute();
        $attribute->setSlug('time');
        $attribute->setValue('Temps partiel (4/5 max)');
        $attribute->setWeight(0);
        $this->manager->persist($attribute);
    }

    private function loadEducations()
    {
        $education = new Education();
        $education->setLevel(2);
        $education->setName('BTS Informatique de Gestion');
        $education->setDateBegin(new \DateTime('2007-09-01 00:00:00'));
        $education->setDateEnd(new \DateTime('2009-08-01 00:00:00'));
        $education->setDetails('Options développeur et math');
        $education->setLocation('Nice');
        $education->setSchool('Lycée Honoré d\'Estienne d\'Orves');
        $this->manager->persist($education);

        $education = new Education();
        $education->setLevel(3);
        $education->setName('LP Systèmes Informatiques et Logiciels');
        $education->setDateBegin(new \DateTime('2009-09-01 00:00:00'));
        $education->setDateEnd(new \DateTime('2010-08-01 00:00:00'));
        $education->setDetails('Option IDSE');
        $education->setLocation('Sophia Antipolis');
        $education->setSchool( 'Institut universitaire de technologie');
        $this->manager->persist($education);
    }

    private function loadHobbies()
    {
        $hobby = new Hobby();
        $hobby->setName('Tir à l\'arc');
        $this->manager->persist($hobby);

        $hobby = new Hobby();
        $hobby->setName('Echasses urbaines');
        $this->manager->persist($hobby);

        $hobby = new Hobby();
        $hobby->setName('Bowling');
        $this->manager->persist($hobby);

        $hobby = new Hobby();
        $hobby->setName('Jeux vidéo');
        $this->manager->persist($hobby);

        $hobby = new Hobby();
        $hobby->setName('Jeux de société');
        $this->manager->persist($hobby);
    }

    private function loadLinks()
    {
        $link = new Link();
        $link->setName('Linkedin');
        $link->setIcon('linkedin');
        $link->setUrl('https://www.linkedin.com/in/achainjeremy');
        $this->manager->persist($link);

        $link = new Link();
        $link->setName('Viadeo');
        $link->setIcon('viadeo');
        $link->setUrl('http://www.viadeo.com/p/002tx09f455dgvf');
        $this->manager->persist($link);

        $link = new Link();
        $link->setName('Hopwork');
        $link->setIcon('wifi');
        $link->setUrl('https://www.hopwork.fr/profile/jeremyachain');
        $this->manager->persist($link);

        $link = new Link();
        $link->setName('Github');
        $link->setIcon('github');
        $link->setUrl('https://github.com/alkemist');
        $this->manager->persist($link);
    }

    private function loadSkills()
    {
        $skill_php = new Skill();
        $skill_php->setName('PHP');
        $skill_php->setLevel(8);
        $skill_php->setOnHomepage(true);
        $skill_php->setType(Skill::TYPE_LANGUAGE);
        $this->manager->persist($skill_php);
        $this->skills['php'] = $skill_php;
        
        $skill_symfony = new Skill();
        $skill_symfony->setName('Symfony');
        $skill_symfony->setLevel(7);
        $skill_symfony->setOnHomepage(true);
        $skill_symfony->setParent($skill_php);
        $skill_symfony->setType(Skill::TYPE_FRAMEWORK);
        $this->manager->persist($skill_symfony);
        $this->skills['symfony'] = $skill_symfony;
        
        $skill_js = new Skill();
        $skill_js->setName('Js');
        $skill_js->setLevel(7);
        $skill_js->setOnHomepage(true);
        $skill_js->setType(Skill::TYPE_LANGUAGE);
        $this->manager->persist($skill_js);
        $this->skills['js'] = $skill_js;
        
        $skill_angular = new Skill();
        $skill_angular->setName('Angular');
        $skill_angular->setLevel(7);
        $skill_angular->setOnHomepage(true);
        $skill_angular->setParent($skill_js);
        $skill_angular->setType(Skill::TYPE_FRAMEWORK);
        $this->manager->persist($skill_angular);
        $this->skills['angular'] = $skill_angular;

        /*$skill = new Skill();
        $skill->setName('Git');
        $skill->setLevel(4);
        $skill->setOnHomepage(false);
        $skill->setType(Skill::TYPE_SOFTWARE);
        $this->manager->persist($skill);
        $this->skills['git'] = $skill;

        $skill = new Skill();
        $skill->setName('Sql');
        $skill->setLevel(6);
        $skill->setOnHomepage(false);
        $skill->setType(Skill::TYPE_SOFTWARE);
        $this->manager->persist($skill);
        $this->skills['sql'] = $skill;*/
    }

    private function loadCompanies()
    {
        $company = new Company();
        $company->setName('CoSpirit');
        $this->manager->persist($company);
        $this->companies['cospirit'] = $company;
    }

    private function loadExperience()
    {
        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['cospirit']);
        $experience->setDateBegin(new \DateTime('2015-08-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2016-04-01 00:00:00'));
        $experience->setDescription('');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $this->manager->persist($experience);
    }
}
