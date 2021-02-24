
<template>
    <div class="recipe">
      <md-button class="md-fab action-back" :href="'/kitchen'">
        <md-icon>arrow_back</md-icon>
      </md-button>
      <md-button class="md-primary md-fab action-zoomi" @click="setZoom(0.1)"><md-icon>zoom_in</md-icon></md-button>
      <md-button class="md-primary md-fab action-zoomd" @click="setZoom(-0.1)"><md-icon>zoom_out</md-icon></md-button>

      <md-card v-if="recipe">
        <md-card-header>
          <md-card-header-text>
            <div class="md-title">{{ recipe.name }}
              <span v-if="recipe.vege">(<md-icon>local_florist</md-icon>
                <span v-if="recipe.vege && !recipe.vegan">Végétarien</span>
                <span v-if="recipe.vegan">Vegan</span>
              )</span>
              <span v-if="recipe.meat || recipe.fish">(<md-icon>goat</md-icon>
                <span v-if="recipe.fish">Poisson</span>
                <span v-if="recipe.meat">Viande</span>
              )</span>
              <span v-if="recipe.nbSlices"><md-icon class="md-size">local_pizza</md-icon><span>{{ recipe.nbSlices }} parts</span></span>
            </div>

          </md-card-header-text>
        </md-card-header>

        <md-card-content v-bind:style="{zoom: zoom}">
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
                </md-button>
              </md-card-actions>
            </md-card>
            <md-card class="info" v-if="recipe.preparationDuration || recipe.cookingDuration || recipe.waitingDuration">
              <md-card-content>
                <div class="md-title" v-if="recipe.preparationDuration"><md-icon class="md-size-2x">av_timer</md-icon><span>{{ minToHour(recipe.preparationDuration) }}</span></div>
                <div class="md-title" v-if="recipe.cookingDuration"><md-icon class="md-size-2x">microwave</md-icon><span>{{ minToHour(recipe.cookingDuration) }}</span></div>
                <div class="md-title" v-if="recipe.waitingDuration"><md-icon class="md-size-2x">snooze</md-icon><span>{{ minToHour(recipe.waitingDuration) }}</span></div>
              </md-card-content>
            </md-card>
          </div>

          <div class="instructions">
            <md-card v-for="(instruction, index) in recipe.instructions" v-bind:key="index" class="instruction">
              <md-card-content>
                <md-badge class="md-primary" :md-content="parseInt(index)+1" />
                {{ instruction }}
              </md-card-content>
            </md-card>
          </div>
        </md-card-content>
      </md-card>
    </div>
</template>

<script>
  import Vue from 'vue';
  import {MdCard, MdButton, MdIcon, MdDialog, MdBadge} from 'vue-material/dist/components';
  import NoSleep from 'nosleep.js';
  import fullscreen from 'vue-fullscreen';
  import 'vue-material/dist/vue-material.min.css';
  import 'vue-material/dist/theme/default-dark.css';
  import tools from '../functions';

  const noSleep = new NoSleep();
  noSleep.enable();

  Vue.use(MdCard);
  Vue.use(MdButton);
  Vue.use(MdIcon);
  Vue.use(MdDialog);
  Vue.use(MdBadge);
  Vue.use(fullscreen);

  export default {
    methods: {
      minToHour(min) {
        return tools.minToHour(min, true, true);
      },
      setZoom(step) {
        if (this.zoom + step > 0.5 && this.zoom + step < 2) {
          this.zoom += step;
        }
      }
    },
    data() {
      return {
        recipe: {},
        zoom: 1,
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
