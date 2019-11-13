<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use App\Entity\Company;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Hobby;
use App\Entity\Invoice;
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
        $this->loadInvoices();

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
        $link->setIcon('fab fa-linkedin');
        $link->setUrl('https://www.linkedin.com/in/achainjeremy');
        $this->manager->persist($link);

        $link = new Link();
        $link->setName('Viadeo');
        $link->setIcon('fab fa-viadeo');
        $link->setUrl('http://www.viadeo.com/p/002tx09f455dgvf');
        $this->manager->persist($link);

        $link = new Link();
        $link->setName('Hopwork');
        $link->setIcon('fas fa-wifi');
        $link->setUrl('https://www.hopwork.fr/profile/jeremyachain');
        $this->manager->persist($link);

        $link = new Link();
        $link->setName('Github');
        $link->setIcon('fab fa-github');
        $link->setUrl('https://github.com/alkemist');
        $this->manager->persist($link);
    }

    private function loadSkills()
    {
        $skill = new Skill();
        $skill->setName('PHP');
        $skill->setLevel(8);
        $skill->setOnHomepage(true);
        $skill->setType(Skill::TYPE_LANGUAGE);
        $this->manager->persist($skill);
        $this->skills['php'] = $skill;
        
        $skill = new Skill();
        $skill->setName('Symfony');
        $skill->setLevel(7);
        $skill->setOnHomepage(true);
        $skill->setParent($this->skills['php']);
        $skill->setType(Skill::TYPE_FRAMEWORK);
        $this->manager->persist($skill);
        $this->skills['symfony'] = $skill;

        $skill = new Skill();
        $skill->setName('CodeIgniter');
        $skill->setLevel(9);
        $skill->setOnHomepage(false);
        $skill->setParent($this->skills['php']);
        $skill->setType(Skill::TYPE_FRAMEWORK);
        $this->manager->persist($skill);
        $this->skills['codeigniter'] = $skill;
        
        $skill = new Skill();
        $skill->setName('Js');
        $skill->setLevel(7);
        $skill->setOnHomepage(true);
        $skill->setType(Skill::TYPE_LANGUAGE);
        $this->manager->persist($skill);
        $this->skills['js'] = $skill;
        
        $skill = new Skill();
        $skill->setName('Angular');
        $skill->setLevel(7);
        $skill->setOnHomepage(true);
        $skill->setParent($this->skills['js']);
        $skill->setType(Skill::TYPE_FRAMEWORK);
        $this->manager->persist($skill);
        $this->skills['angular'] = $skill;

        $skill = new Skill();
        $skill->setName('NodeJS');
        $skill->setLevel(4);
        $skill->setOnHomepage(false);
        $skill->setParent($this->skills['js']);
        $skill->setType(Skill::TYPE_FRAMEWORK);
        $this->manager->persist($skill);
        $this->skills['nodejs'] = $skill;

        $skill = new Skill();
        $skill->setName('ExtJS');
        $skill->setLevel(4);
        $skill->setOnHomepage(false);
        $skill->setParent($this->skills['js']);
        $skill->setType(Skill::TYPE_FRAMEWORK);
        $this->manager->persist($skill);
        $this->skills['extjs'] = $skill;

        $skill = new Skill();
        $skill->setName('jQuery');
        $skill->setLevel(4);
        $skill->setOnHomepage(false);
        $skill->setParent($this->skills['js']);
        $skill->setType(Skill::TYPE_FRAMEWORK);
        $this->manager->persist($skill);
        $this->skills['jquery'] = $skill;

        $skill = new Skill();
        $skill->setName('Git');
        $skill->setLevel(4);
        $skill->setOnHomepage(false);
        $skill->setType(Skill::TYPE_SOFTWARE);
        $this->manager->persist($skill);
        $this->skills['git'] = $skill;
    }

    private function loadCompanies()
    {
        $company = new Company();
        $company->setName('NETexcom');
        $company->setLocation('Monaco');
        $this->manager->persist($company);
        $this->companies['netexcom'] = $company;

        $company = new Company();
        $company->setName('Eleusis Solution');
        $company->setLocation('Nice');
        $this->manager->persist($company);
        $this->companies['eleusis'] = $company;

        $company = new Company();
        $company->setName('ACL Informatique');
        $company->setLocation('Villeneuve-Loubet');
        $this->manager->persist($company);
        $this->companies['acl'] = $company;

        $company = new Company();
        $company->setName('e-Toxic');
        $company->setLocation('Villeneuve-Loubet');
        $this->manager->persist($company);
        $this->companies['etoxic'] = $company;

        $company = new Company();
        $company->setName('Qwant');
        $company->setLocation('Nice');
        $this->manager->persist($company);
        $this->companies['qwant'] = $company;

        $company = new Company();
        $company->setName('Audivox');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['audivox'] = $company;

        $company = new Company();
        $company->setName('Audiovisit');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['audiovisit'] = $company;

        $company = new Company();
        $company->setName('Opusline');
        $company->setLocation('Paris');
        $this->manager->persist($company);
        $this->companies['opusline'] = $company;

        $company = new Company();
        $company->setName('Talkspirit');
        $company->setLocation('Paris');
        $this->manager->persist($company);
        $this->companies['talkspirit'] = $company;

        $company = new Company();
        $company->setName('Jessica Rolland');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['girlstakelyon'] = $company;

        $company = new Company();
        $company->setName('Leidgens Groupe');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['leidgens'] = $company;

        $company = new Company();
        $company->setName('Pretty Cool');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['prettycool'] = $company;

        $company = new Company();
        $company->setName('Guillaume Ribot');
        $company->setLocation('Paris');
        $this->manager->persist($company);
        $this->companies['ribot'] = $company;

        $company = new Company();
        $company->setName('CoSpirit');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['cospirit'] = $company;

        $company = new Company();
        $company->setName('Ar Developpement');
        $company->setLocation('Lyon');
        $company->setContractor($this->companies['cospirit']);
        $this->manager->persist($company);
        $this->companies['ar'] = $company;

        $company = new Company();
        $company->setName('Altima');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['altima'] = $company;

        $company = new Company();
        $company->setName('Obiz');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['obiz'] = $company;

        $company = new Company();
        $company->setName('Agixis');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['agixis'] = $company;

        $company = new Company();
        $company->setName('Spyrit');
        $company->setLocation('Viroflay');
        $this->manager->persist($company);
        $this->companies['spyrit'] = $company;

        $company = new Company();
        $company->setName('Helfrich');
        $company->setContractor($this->companies['spyrit']);
        $this->manager->persist($company);
        $this->companies['helfrich'] = $company;

        $company = new Company();
        $company->setName('La compagnie hyperactive');
        $company->setLocation('Paris');
        $this->manager->persist($company);
        $this->companies['hyperactive'] = $company;

        $company = new Company();
        $company->setName('Drakona');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['drakona'] = $company;

        $company = new Company();
        $company->setName('CS Systemes D\'information');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['cs'] = $company;

        $company = new Company();
        $company->setName('Inpact');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['inpact'] = $company;

        $company = new Company();
        $company->setName('EDF Septen');
        $company->setLocation('Villeurbanne');
        $this->manager->persist($company);
        $company->setContractor($this->companies['cs']);
        $this->companies['edf-septen'] = $company;

        $company = new Company();
        $company->setName('Apollo SSC');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['apollo'] = $company;

        $company = new Company();
        $company->setName('Cegid');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $company->setContractor($this->companies['apollo']);
        $this->companies['cegid'] = $company;

        $company = new Company();
        $company->setName('Aleysia');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['aleysia'] = $company;

        $company = new Company();
        $company->setName('Ucly');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $company->setContractor($this->companies['aleysia']);
        $this->companies['ucly'] = $company;

        $company = new Company();
        $company->setName('BeWizYu');
        $company->setLocation('Lyon');
        $this->manager->persist($company);
        $this->companies['bewizyu'] = $company;

        $company = new Company();
        $company->setName('Acte Media');
        $company->setLocation('Bron');
        $this->manager->persist($company);
        $company->setContractor($this->companies['bewizyu']);
        $this->companies['actemedia'] = $company;

        $company = new Company();
        $company->setName('Marquetis');
        $this->manager->persist($company);
        $company->setContractor($this->companies['actemedia']);
        $this->companies['marquetis'] = $company;

        $company = new Company();
        $company->setName('La Poste');
        $this->manager->persist($company);
        $company->setContractor($this->companies['marquetis']);
        $this->companies['laposte'] = $company;
    }

    private function loadExperience()
    {
        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['netexcom']);
        $experience->setDateBegin(new \DateTime('2009-08-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2010-08-01 00:00:00'));
        $experience->setDescription('Développement d\'un projet de CRM web "From Scratch", encadré par un directeur technique, pour faciliter la gestion des commerciaux de l\'entreprise.');
        $experience->setIsFreelance(false);
        $experience->setOnSite(true);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['php']);
        $experience->addSkill($this->skills['js']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['eleusis']);
        $experience->setDateBegin(new \DateTime('2010-09-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2010-12-01 00:00:00'));
        $experience->setDescription('Développement de script PHP, mise en place de site');
        $experience->setIsFreelance(false);
        $experience->setOnSite(false);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['php']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['acl']);
        $experience->setDateBegin(new \DateTime('2011-02-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2012-09-01 00:00:00'));
        $experience->setDescription('Intégration dans une équipe de 2 développeurs sur un projet d\'application web de gestion de comités d\'entreprise');
        $experience->setIsFreelance(false);
        $experience->setOnSite(true);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['php']);
        $experience->addSkill($this->skills['extjs']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['etoxic']);
        $experience->setDateBegin(new \DateTime('2012-12-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2013-04-01 00:00:00'));
        $experience->setDescription('Intégration au sein d’une équipe de 4 développeurs travaillant sur l’amélioration de ForumActif');
        $experience->setIsFreelance(false);
        $experience->setOnSite(true);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['php']);
        $experience->addSkill($this->skills['extjs']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['qwant']);
        $experience->setDateBegin(new \DateTime('2013-05-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2014-01-01 00:00:00'));
        $experience->setDescription('Participation au premier lancement officiel, développement du module des carnets, et amélioration des services connexes au moteur de recherche.');
        $experience->setIsFreelance(false);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['jquery']);
        $experience->addSkill($this->skills['codeigniter']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['audivox']);
        $experience->setDateBegin(new \DateTime('2014-04-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2014-09-01 00:00:00'));
        $experience->setDescription('Utilisation d\' API de solution de payement mobile et de fonctionnement audio via le navigateur.');
        $experience->setIsFreelance(false);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['audiovisit']);
        $experience->setDateBegin(new \DateTime('2015-02-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2015-03-01 00:00:00'));
        $experience->setDescription('Développement d\'un backoffice de gestion de contenu avec API pour accéder au contenu via des applications mobiles. Seul développeur encadré par le seul développeur de l\'entreprise, qui a la charge du développement des applications mobiles.');
        $experience->setIsFreelance(false);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['opusline']);
        $experience->setDateBegin(new \DateTime('2015-05-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2015-07-01 00:00:00'));
        $experience->setDescription('Développement d\'une application de traitement/filtrage de donnée importé et d\'affichage de statistiques. Analyse des besoins et solutions à apporter avec l’intermédiaire technique qui revendra l\'application à des assurances.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(false);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['talkspirit']);
        $experience->setDateBegin(new \DateTime('2015-04-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2015-04-01 00:00:00'));
        $experience->setDescription('Soutien sur un projet de plateforme collaborative');
        $experience->setIsFreelance(true);
        $experience->setOnSite(false);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['angular']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['ar']);
        $experience->setDateBegin(new \DateTime('2015-08-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2016-04-01 00:00:00'));
        $experience->setDescription('Intégration dans une équipe de 4 développeurs pour le soutien dans le développement d’une application de gestion de support de communication. Abstraction complexe de l\'architecture du projet, avec plusieurs projets qui communique entre eux (API, Assets, Front, Core).');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['altima']);
        $experience->setDateBegin(new \DateTime('2016-06-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2016-06-01 00:00:00'));
        $experience->setDescription('Corrections et ajout de fonctionnalités sur un site eCommerce.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['nodejs']);
        $experience->addSkill($this->skills['js']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['obiz']);
        $experience->setDateBegin(new \DateTime('2016-07-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2016-08-01 00:00:00'));
        $experience->setDescription('Développement d\'un backoffice pour la gestion de notifications mail/push. Connexion à une base de donnée d\'une application CRM existante développée par et moteur de l\'entreprise. Seul sur le projet sous la direction du directeur technique.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['agixis']);
        $experience->setDateBegin(new \DateTime('2016-11-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2016-11-01 00:00:00'));
        $experience->setDescription('Soutien Front sur un projet de gestion de catalogue. Le projet date de plusieurs mois par un développeur qui n\'est plus dans l\'équipe. Seul développeur sur le projet, dans les locaux de l\'ESN. Amélioration des fonctionnalités existantes sous la direction du chef de projet.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['angular']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['helfrich']);
        $experience->setDateBegin(new \DateTime('2016-12-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2017-05-01 00:00:00'));
        $experience->setDescription('Développement de fonctionnalités Back et/ou Front sur un projet de plateforme de gestion de comité d\'entreprise. Le projet a plus d\'un an, avec la participation de 2 autres entreprises travaillant à distance, à destination d\'un client final qui revendra la solution en mode SAS. L\'équipe est constitué de 4 développeur(e)s. La gestion de projet est en mode agile, avec des sprints en 2 ou 3 semaines.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(false);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['hyperactive']);
        $experience->setDateBegin(new \DateTime('2017-07-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2017-07-01 00:00:00'));
        $experience->setDescription('Soutient sur le développement d\'un CRM pour une agence web. Développement et amélioration de fonctionnalités, guidé par le directeur technique de l\'agence.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(false);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['jquery']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['edf-septen']);
        $experience->setDateBegin(new \DateTime('2017-09-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2017-10-01 00:00:00'));
        $experience->setDescription('Développement d\'un POC, coté front, pour la gestion des données lié à la sûreté des centrales nucléaires. L\'équipe est constitué d\'un autre développeur, JAVA, qui s\'est occupé de faire l\'API REST et la gestion de projet fait par une ESN, qui a fait appel à nous. Analyse des besoins techniques et fonctionnels fait en amont avec le client final.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['angular']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['inpact']);
        $experience->setDateBegin(new \DateTime('2017-11-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2017-11-01 00:00:00'));
        $experience->setDescription('Soutien sur un projet de gestion de facturation.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['helfrich']);
        $experience->setDateBegin(new \DateTime('2017-12-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2018-01-01 00:00:00'));
        $experience->setDescription('Développement de fonctionnalités Back et/ou Front sur un projet de plateforme de gestion de comité d\'entreprise. Le projet a plus d\'un an, avec la participation de 2 autres entreprises travaillant à distance, à destination d\'un client final qui revendra la solution en mode SAS. L\'équipe est constitué de 4 développeur(e)s. La gestion de projet est en mode agile, avec des sprints en 2 ou 3 semaines.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(false);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['drakona']);
        $experience->setDateBegin(new \DateTime('2018-04-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2018-04-01 00:00:00'));
        $experience->setDescription('Développement d\'un outil de gestion et d\'import de donnée.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(false);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['cegid']);
        $experience->setDateBegin(new \DateTime('2018-05-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2019-02-01 00:00:00'));
        $experience->setDescription('Développement d\'une application de gestion RH, POC sur les 3 premier mois, puis d\'un MVP sur la suite. Le projet avait commencé depuis seulement 1 mois, avec une équipe de 3 développeurs (uniquement back), puis s\'est agrandit à une équipe de 8 développeurs (6 back et 2 autres front).');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['angular']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['ucly']);
        $experience->setDateBegin(new \DateTime('2019-04-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2019-05-01 00:00:00'));
        $experience->setDescription('Soutien dans une équipe de 3 développeurs. Ajout de nouvelle fonctionnalité dans le site d\'inscription.');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['js']);
        $experience->addSkill($this->skills['php']);
        $this->manager->persist($experience);

        $experience = new Experience();
        $experience->setTitle('Développeur Web');
        $experience->setCompany($this->companies['laposte']);
        $experience->setDateBegin(new \DateTime('2019-06-01 00:00:00'));
        $experience->setDateEnd(new \DateTime('2019-12-01 00:00:00'));
        $experience->setDescription('Ajout de fonctionnalité sur une application de gestion existante');
        $experience->setIsFreelance(true);
        $experience->setOnSite(true);
        $experience->setOnHomepage(true);
        $experience->addSkill($this->skills['symfony']);
        $experience->addSkill($this->skills['angular']);
        $experience->addSkill($this->skills['git']);
        $this->manager->persist($experience);
    }

    private function loadInvoices()
    {
        // 2015

        $invoice = new Invoice();
        $invoice->setNumber('201505-1');
        $invoice->setCompany($this->companies['opusline']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(3000);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201505-2');
        $invoice->setCompany($this->companies['opusline']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(600);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201505-3');
        $invoice->setCompany($this->companies['opusline']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(600);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201505-4');
        $invoice->setCompany($this->companies['leidgens']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(450);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201505-5');
        $invoice->setCompany($this->companies['talkspirit']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(1500);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201506-1');
        $invoice->setCompany($this->companies['audiovisit']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(150);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201506-2');
        $invoice->setCompany($this->companies['opusline']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(150);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201506-3');
        $invoice->setCompany($this->companies['girlstakelyon']);
        $invoice->setTjm(200);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(200);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201507-1');
        $invoice->setCompany($this->companies['opusline']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(2100);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201509-1');
        $invoice->setCompany($this->companies['ar']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(7200);
        $invoice->setPayedBy(Invoice::PAYEDBY_TRANSFERT);
        $this->manager->persist($invoice);

        $invoice = new Invoice();
        $invoice->setNumber('201510-1');
        $invoice->setCompany($this->companies['ar']);
        $invoice->setTjm(300);
        $invoice->setCreatedAt(new \DateTime('2015-05-04 00:00:00'));
        $invoice->setPayedAt(new \DateTime('2015-05-19 00:00:00'));
        $invoice->setObject('Prestation de développement web');
        $invoice->setTotalHt(6000);
        $invoice->setPayedBy(Invoice::PAYEDBY_CHECK);
        $this->manager->persist($invoice);

        // 2016

        // 2017

        // 2018

        // 2019
    }
}

























