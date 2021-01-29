
<template>
    <div class="recipe">
      <md-button class="md-fab" :href="'/kitchen'">
        <md-icon>arrow_back</md-icon>
      </md-button>
      <md-card v-if="recipe">
        <md-card-header>
          <md-card-header-text>
            <div class="md-title">{{ recipe.name }}</div>
          </md-card-header-text>
        </md-card-header>

        <md-card-content>
          <div v-for="recipeIngredient in recipe.recipeIngredients" v-bind:key="recipeIngredient.id">
            {{ recipeIngredient.ingredient.name }} ({{ recipeIngredient.quantity }}) {{ recipeIngredient.ingredient.typeName }}
          </div>
          <div v-for="instruction in recipe.instructions">
            {{ instruction }}
          </div>
          {{ recipe.cookingDuration }}
          {{ recipe.preparationDuration }}
          {{ recipe.nbSlices }}
          {{ recipe.vegan }}
          {{ recipe.vege }}
        </md-card-content>
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
              recipe: {},
          };
      },
      mounted() {
          let el = document.querySelector("div[data-recipe]");
          this.recipe = JSON.parse(el.dataset.recipe);
      },
      components: {

      }
  };
</script>
