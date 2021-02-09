# Resume

### TODO

- Rapport
    - Envoi d'un compte rendu
    
- Entreprises
    - Possibilité d'envoyer un mail à une entreprise
    
- Déclarations
    - Rentrer dans les fixtures les impots 2016 et 2017 (voir montant à mettre à jour suite à la régularisation)

- Notifications
    - Impots
        - Ajouter les dates
        - Ajouter les notifications

- Purchase
    - Déduire la TVA des achats

- General
    - Documentation
    - Tests
    
- Symfony
    - Event
    - Message
    
### Installation

- symfony composer install
- symfony doctrine:database:create
- symfony doctrine:schema:update --force
- symfony doctrine:fixtures:load --env=dev
- yarn
- symfony server:start
- yarn encore dev --watch

# Kitchen Party

### Features

- Liste des recettes  
- Filtre par nom et ingrédients
- Selection multiple pour génération de liste de courses (regroupement des ingrédients, calcul du nombre de parts)  
- Vue recette
- Enregistrement d'une image par recette

### TODO

- Filtre options vegan/végé
- Filtre plat/dessert/"pâte"
- recherche en fonction du "frigo"
- Tri par temps de préparation et temps de cuisson  
