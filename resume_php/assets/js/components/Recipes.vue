<template>
  <div class="page-container">
    <md-app md-waterfall md-mode="fixed">
      <md-app-toolbar>
        <div class="md-toolbar-row">
          <div class="md-toolbar-section-start">

          </div>

          <md-field md-layout="box" class="search-field">
            <label>Nom</label>
            <md-input v-model="form.search"></md-input>
          </md-field>

          <multiselect class="search-field" v-model="form.ingredients" :options="ingredients" :multiple="true"
                       placeholder="Ingredients"
                       selectLabel="Selection de l'ingrédient"
                       deselectLabel="Supprimer l'ingrédient"
                       selectedLabel="Selectionné"
                       track-by="name" label="name" >
              <span slot="noResult">Aucun ingrédient trouvé</span>
          </multiselect>

          <div class="md-toolbar-section-end">

          </div>
        </div>
      </md-app-toolbar>
      <md-app-content class="recipes">
        <md-card v-for="recipe in listRecipes()" v-bind:key="recipe.id" class="recipe" >
          <md-card-header>
            <md-card-header-text>
              <div class="md-title">{{ recipe.name }}</div>
            </md-card-header-text>
          </md-card-header>

          <md-card-expand>
            <md-card-expand-content>
              <md-card-content>
                <div v-for="recipeIngredient in recipe.recipeIngredients" v-bind:key="recipeIngredient.id">
                  <md-icon v-if="recipeIngredient.ingredient.type === 'meat'">goat</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'fish_seafood'">directions_boat</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'fruit_vegetable_mushroom'">local_florist</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'cereal_legume'">grass</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'animal_fat'">opacity</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'vegetable_fat'">opacity</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'yeast'">bubble_chart</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'aromatic_herb'">eco</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'spice'">bolt</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'sugar'">view_comfortable</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'salt'">grain</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'alcohol'">liquor</md-icon>
                  <md-icon v-if="recipeIngredient.ingredient.type === 'water'">local_drink</md-icon>
                  <span>{{ recipeIngredient.name }}</span>
                </div>
              </md-card-content>
            </md-card-expand-content>

            <md-card-actions md-alignment="space-between">
              <div>
                <md-button class="md-fab" :md-ripple="false" style="color: white;" v-bind:class="{type_meat: recipe.meat, type_fish: recipe.fish, type_vege: recipe.vege && !recipe.vegan, type_vegan: recipe.vegan}">
                  <md-icon v-if="recipe.meat">goat</md-icon>
                  <md-icon v-if="recipe.fish">directions_boat</md-icon>
                  <md-icon v-if="recipe.vege && !recipe.vegan">goat</md-icon>
                  <md-icon v-if="recipe.vegan">eco</md-icon>
                </md-button>
                <md-button v-if="recipe.preparationDuration" class="md-fab" :md-ripple="false" style="background-color: white;">
                  <md-icon style="color: black;">av_timer</md-icon><span>{{ minToHour(recipe.preparationDuration) }}</span>
                </md-button>
                <md-button v-if="recipe.cookingDuration" class="md-fab" :md-ripple="false" style="background-color: white;">
                  <md-icon style="color: black;">microwave</md-icon><span>{{ minToHour(recipe.cookingDuration) }}</span>
                </md-button>
                <md-button v-if="recipe.waitingDuration" class="md-fab" :md-ripple="false" style="background-color: white;">
                  <md-icon style="color: black;">snooze</md-icon><span>{{ minToHour(recipe.waitingDuration) }}</span>
                </md-button>
                <md-button v-if="recipe.nbSlices" class="md-fab" :md-ripple="false" style="background-color: white;">
                  <md-icon style="color: black;">local_pizza</md-icon><span>{{ recipe.nbSlices }}</span>
                </md-button>
                <md-card-expand-trigger>
                  <md-button class="md-icon-button">
                    <md-icon>keyboard_arrow_down</md-icon>
                  </md-button>
                </md-card-expand-trigger>
              </div>
              <div>
                <md-button class="md-fab" :href="'/kitchen/' + recipe.id" v-bind:class="{type_sugar: recipe.sweet, type_salt: recipe.salty, 'bg_black': !recipe.sweet && !recipe.salty }">
                  <md-icon>local_dining</md-icon>
                </md-button>
              </div>
            </md-card-actions>
          </md-card-expand>
        </md-card>
      </md-app-content>
    </md-app>
  </div>
</template>

<script>
  import Vue from 'vue';
  import {
    MdCard,
    MdButton,
    MdIcon,
    MdToolbar,
    MdApp,
    MdContent,
    MdField,
    MdAutocomplete, MdMenu, MdHighlightText, MdList
  } from 'vue-material/dist/components';
  import NoSleep from 'nosleep.js';
  import Multiselect from 'vue-multiselect'
  import tools from '../functions';

  import 'vue-material/dist/vue-material.min.css';
  import 'vue-material/dist/theme/default-dark.css';
  import "vue-multiselect/dist/vue-multiselect.min.css";

  const noSleep = new NoSleep();
  noSleep.enable();

  Vue.use(MdApp);
  Vue.use(MdToolbar);
  Vue.use(MdContent);

  Vue.use(MdCard);
  Vue.use(MdButton);
  Vue.use(MdIcon);

  Vue.use(MdField);

  // register globally

  export default {
    components: {
      Multiselect
    },
    methods: {
      listRecipes() {
        const recipes = [];
        const selectedIngredientIds = this.form.ingredients.reduce((acc, current) => {
          acc.push(current.id);
          return acc;
        }, []);

        this.recipes.forEach(recipe => {
          if (
              (this.form.ingredients.length === 0 || selectedIngredientIds.every(id => recipe.ingredientIds.indexOf(id) > -1))
              && tools.normalize(recipe.name).search(this.form.search) > -1
          ) {
            recipes.push(recipe);
          }
        });

        recipes.sort((recipeA, recipeB) => {
          let sortA = 0;
          let sortB = 0;

          if (recipeA.salty) sortA = 1;
          if (recipeB.salty) sortB = 1;
          if (recipeA.sweet) sortA = -1;
          if (recipeB.sweet) sortB = -1;

          if (sortA === sortB) {
            if (recipeA.vegan) sortA = 4;
            if (recipeB.vegan) sortB = 4;
            if (recipeA.vege) sortA = 3;
            if (recipeB.vege) sortB = 3;
            if (recipeA.meat) sortA = 2;
            if (recipeB.meat) sortB = 2;
            if (recipeA.fish) sortA = 1;
            if (recipeB.fish) sortB = 1;
          }

          return sortB - sortA;
        });

        return recipes;
      },
      minToHour(min) {
        return tools.minToHour(min, true);
      }
    },
    data() {
      return {
        form : {
          search: '',
          ingredients: []
        },
        recipes: [],
        ingredients: [],
      };
    },
    mounted() {
      let elRecipes = document.querySelector("div[data-recipes]");
      let elIngredients = document.querySelector("div[data-ingredients]");
      this.recipes = JSON.parse(elRecipes.dataset.recipes);
      this.ingredients = JSON.parse(elIngredients.dataset.ingredients);
      console.log(this.recipes)
      console.log(this.ingredients)
    },
  };
</script>
