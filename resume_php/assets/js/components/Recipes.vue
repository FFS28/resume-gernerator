
<template>
    <div class="recipes">
      <md-card v-for="recipe in recipes" v-bind:key="recipe.id" class="recipe" >
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
                <md-icon v-if="recipeIngredient.ingredient.type === 'fruit_vegetable'">local_florist</md-icon>
                <md-icon v-if="recipeIngredient.ingredient.type === 'cereal_legume'">grass</md-icon>
                <md-icon v-if="recipeIngredient.ingredient.type === 'animal_fat'">opacity</md-icon>
                <md-icon v-if="recipeIngredient.ingredient.type === 'vegetable_fat'">opacity</md-icon>
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
              <md-button class="md-fab" style="background-color: white;">
                <md-icon style="color: black;">microwave</md-icon>{{ recipe.cookingDuration }}</small>
              </md-button>
              <md-button class="md-fab" style="background-color: white;">
                <md-icon style="color: black;">av_timer</md-icon>{{ recipe.preparationDuration }}</small>
              </md-button>
              <md-button class="md-fab" style="background-color: white;">
                <md-icon style="color: black;">local_pizza</md-icon>{{ recipe.nbSlices }}</small>
              </md-button>
              <md-card-expand-trigger>
                <md-button class="md-icon-button">
                  <md-icon>keyboard_arrow_down</md-icon>
                </md-button>
              </md-card-expand-trigger>
            </div>
            <div>
              <md-button class="md-fab md-primary" :href="'/kitchen/' + recipe.id">
                <md-icon>local_dining</md-icon>
              </md-button>
            </div>
          </md-card-actions>
        </md-card-expand>
      </md-card>
    </div>
</template>

<script>
  import Vue from 'vue';
  import {MdCard, MdButton, MdIcon} from 'vue-material/dist/components';
  import 'vue-material/dist/vue-material.min.css';
  import 'vue-material/dist/theme/default.css';

  Vue.use(MdCard);
  Vue.use(MdButton);
  Vue.use(MdIcon);

  export default {
    data() {
      return {
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
    components: {

    }
  };
</script>
