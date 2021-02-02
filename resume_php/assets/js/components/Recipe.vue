
<template>
    <div class="recipe">
      <md-button class="md-fab" :href="'/kitchen'">
        <md-icon>arrow_back</md-icon>
      </md-button>
      <md-button class="md-primary md-fab" @click="showDialog = true"><md-icon>zoom_in</md-icon></md-button>

      <md-card v-if="recipe">
        <md-card-header>
          <md-card-header-text>
            <div class="md-title">{{ recipe.name }}
              <span v-if="recipe.vege">(<md-icon>local_florist</md-icon>
                <span v-if="recipe.vege && !recipe.vegan">Végétarien</span>
                <span v-if="recipe.vegan">Vegan</span>
              )</span>
              <span v-if="recipe.meat || recipe.fish">(<md-icon>goat</md-icon>
                <span v-if="recipe.meat">Poisson</span>
                <span v-if="recipe.fish">Viande</span>
              )</span>
            </div>

          </md-card-header-text>
        </md-card-header>

        <md-card-content>
          <div class="ingredients">
            <md-card v-for="recipeIngredient in recipe.recipeIngredients" v-bind:key="recipeIngredient.id" class="ingredient md-elevation-6">
              <md-card-header>
                <div class="md-title">{{ recipeIngredient.ingredient.name }}</div>
                <div class="md-subhead">{{ recipeIngredient.measureStr }}</div>
              </md-card-header>
              <md-card-actions md-alignment="space-between">
                <md-button class="md-fab" :md-ripple="false" :class="'type_' + recipeIngredient.ingredient.type">
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
                </md-button>
              </md-card-actions>
            </md-card>
            <md-card class="info">
              <md-card-content>
                <div class="md-title" v-if="recipe.cookingDuration"><md-icon class="md-size-2x">microwave</md-icon><span>{{ recipe.cookingDuration }} min</span></div>
                <div class="md-title" v-if="recipe.preparationDuration"><md-icon class="md-size-2x">av_timer</md-icon><span>{{ recipe.preparationDuration }} min</span></div>
                <div class="md-title" v-if="recipe.nbSlices"><md-icon class="md-size-2x">local_pizza</md-icon><span>{{ recipe.nbSlices }}</span></div>
              </md-card-content>
            </md-card>
          </div>

          <div class="instructions">
            <md-card v-for="(instruction, index) in recipe.instructions" v-bind:key="index" class="instruction">
              <md-card-content>
                {{ instruction }}
              </md-card-content>
            </md-card>
          </div>
        </md-card-content>
      </md-card>
      <md-dialog :md-active.sync="showDialog">
        <div class="instructions">
          <md-card v-for="(instruction, index) in recipe.instructions" v-bind:key="index" class="instruction">
            <md-card-content>
              {{ instruction }}
            </md-card-content>
          </md-card>
        </div>
        <md-dialog-actions>
          <md-button class="md-primary" @click="showDialog = false"><md-icon>close</md-icon></md-button>
        </md-dialog-actions>
      </md-dialog>
    </div>
</template>

<script>
  import Vue from 'vue';
  import {MdCard, MdButton, MdIcon, MdDialog} from 'vue-material/dist/components';
  import NoSleep from 'nosleep.js';
  import 'vue-material/dist/vue-material.min.css';
  import 'vue-material/dist/theme/default.css';

  const noSleep = new NoSleep();
  noSleep.enable();

  Vue.use(MdCard);
  Vue.use(MdButton);
  Vue.use(MdIcon);
  Vue.use(MdDialog);

  export default {
    data() {
      return {
        showDialog: false,
        recipe: {},
      };
    },
    mounted() {
      let el = document.querySelector("div[data-recipe]");
      this.recipe = JSON.parse(el.dataset.recipe);
      console.log(this.recipe);
    },
    components: {

    }
  };
</script>
