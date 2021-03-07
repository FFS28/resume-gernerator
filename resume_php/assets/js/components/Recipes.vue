<template>
  <div class="page-container">
    <md-app md-waterfall md-mode="fixed">
      <md-app-toolbar>
        <div class="md-toolbar-row" >
          <div class="md-toolbar-section-start" v-if="$isMobile()">
            <md-button class="md-icon-button menu-button" @click="filtersShowed = true">
              <md-icon>menu</md-icon>
            </md-button>
            <span class="md-title">Kitchen Party</span>
          </div>
          <div class="md-toolbar-section-start" v-if="!$isMobile()">
            <md-field class="field-type">
              <label for="type">Type</label>
              <md-select v-model="form.type" name="type" id="type">
                <md-option value="">Tous</md-option>
                <md-option value="none">Neutre</md-option>
                <md-option value="salty">Salé</md-option>
                <md-option value="sweet">Sucré</md-option>
              </md-select>
            </md-field>
            <md-field class="field-diet">
              <label for="diet">Régime</label>
              <md-select v-model="form.diet" name="diet" id="diet">
                <md-option value="">Tous</md-option>
                <md-option value="vegan">Vegan</md-option>
                <md-option value="vege">Végé</md-option>
                <md-option value="meat">Viande</md-option>
              </md-select>
            </md-field>

            <md-field md-layout="box" class="field-name" md-clearable>
              <label>Nom</label>
              <md-input v-model="form.search"></md-input>
            </md-field>
          </div>

          <multiselect v-if="!$isMobile()" class="field-ingredients" v-model="form.ingredients" :options="ingredientsByTypes" :multiple="true"
                       placeholder="Ingredients"
                       selectLabel="Selection de l'ingrédient"
                       deselectLabel="Supprimer l'ingrédient"
                       selectedLabel="Selectionné"
                       track-by="name" label="name"
                       group-label="name" group-values="ingredients" :group-select="false">
              <span slot="noResult">Aucun ingrédient trouvé</span>
          </multiselect>

          <div class="md-toolbar-section-end">
            <md-button class="md-raised md-icon-button" v-on:click="clearForm()" v-if="!$isMobile()">
              <md-icon>clear</md-icon>
            </md-button>
            <md-button class="md-primary md-raised md-icon-button" v-on:click="goToShopping()" :disabled="form.selectedRecipes.length === 0">
              <md-icon>shopping_cart</md-icon>
            </md-button>
          </div>
        </div>
      </md-app-toolbar>
      <md-app-content class="recipes">
        <md-drawer :md-active.sync="filtersShowed" md-swipeable>
          <md-field class="field-type">
            <label for="type">Type</label>
            <md-select v-model="form.type" name="type" id="type">
              <md-option value="">Tous</md-option>
              <md-option value="none">Neutre</md-option>
              <md-option value="salty">Salé</md-option>
              <md-option value="sweet">Sucré</md-option>
            </md-select>
          </md-field>
          <md-field class="field-diet">
            <label for="diet">Régime</label>
            <md-select v-model="form.diet" name="diet" id="diet">
              <md-option value="">Tous</md-option>
              <md-option value="vegan">Vegan</md-option>
              <md-option value="vege">Végé</md-option>
              <md-option value="meat">Viande</md-option>
            </md-select>
          </md-field>

          <md-field md-layout="box" class="field-name" md-clearable>
            <label>Nom</label>
            <md-input v-model="form.search"></md-input>
          </md-field>

          <multiselect class="field-ingredients" v-model="form.ingredients" :options="ingredientsByTypes" :multiple="true"
                       placeholder="Ingredients"
                       selectLabel="Selection de l'ingrédient"
                       deselectLabel="Supprimer l'ingrédient"
                       selectedLabel="Selectionné"
                       track-by="name" label="name"
                       group-label="name" group-values="ingredients" :group-select="false">
            <span slot="noResult">Aucun ingrédient trouvé</span>
          </multiselect>

          <md-button class="md-accent" v-on:click="clearForm()">
            Tous afficher
          </md-button>
        </md-drawer>

        <md-card v-for="recipe in listRecipes()" v-bind:key="recipe.id" class="recipe" v-bind:class="{hasPhoto: recipe.image}" >
          <md-card-media v-if="recipe.image">
            <div class="center-cropped" v-on:click="showModal(recipe)"
                 v-bind:style="{'background-image': 'url(\'' + recipe.imagePath + '\')'}">
              <img :src="recipe.imagePath" />
            </div>
          </md-card-media>

          <div class="card-informations">
            <md-card-header>
              <md-checkbox v-model="form.selectedRecipes" id="selectRecipe" :value="recipe" class="md-primary"></md-checkbox>
              <md-card-header-text>
                <div class="md-title" for="selectRecipe">{{ recipe.name }}</div>
              </md-card-header-text>
            </md-card-header>

            <md-card-expand>
              <md-card-expand-content>
                <md-card-content>
                  <div v-for="recipeIngredient in recipe.recipeIngredients" v-bind:key="recipeIngredient.id" v-bind:style="{color: recipeIngredient.kitchen ? '#448aff' : 'white'}">
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
                  <div class="diet">
                    <md-icon v-if="recipe.meat" style="color: #e53935;">goat</md-icon>
                    <md-icon v-if="recipe.fish" style="color: #1e88e5;">directions_boat</md-icon>
                    <md-icon v-if="recipe.vege && !recipe.vegan" style="color: #689f38;">goat</md-icon>
                    <md-icon v-if="recipe.vegan" style="color: #1b5e20;">eco</md-icon>
                  </div>
                  <div class="info" v-if="recipe.preparationDuration">
                    <md-icon>av_timer</md-icon><span>{{ minToHour(recipe.preparationDuration) }}</span>
                  </div>
                  <div class="info" v-if="recipe.cookingDuration">
                    <md-icon>microwave</md-icon><span>{{ minToHour(recipe.cookingDuration) }}</span>
                  </div>
                  <div class="info" v-if="recipe.waitingDuration">
                    <md-icon>snooze</md-icon><span>{{ minToHour(recipe.waitingDuration) }}</span>
                  </div>
                  <div class="info" v-if="recipe.nbSlices">
                    <md-icon>local_pizza</md-icon><span>{{ recipe.nbSlices }}</span>
                  </div>
                  <div class="info" v-if="recipe.kitchen" v-bind:class="{ allKitchen: recipe.allKitchen }">
                    <md-icon>inventory</md-icon><span>{{ recipe.kitchen }}</span>
                  </div>
                  <md-card-expand-trigger>
                    <md-button class="md-icon-button">
                      <md-icon>keyboard_arrow_down</md-icon>
                    </md-button>
                  </md-card-expand-trigger>
                </div>
                <div>
                  <md-button class="md-fab md-primary" :href="'/kitchen/' + recipe.slug">
                    <md-icon style="color: #000;">local_dining</md-icon>
                  </md-button>
                </div>
              </md-card-actions>
            </md-card-expand>
          </div>
        </md-card>
      </md-app-content>
    </md-app>
    <md-dialog :md-active.sync="lightboxShowed">
      <div class="center-cropped" @click="lightboxShowed = false;"
           v-bind:style="{'background-image': 'url(\'' + (selectedRecipe ? selectedRecipe.imagePath : '') + '\')'}">
        <img :src="selectedRecipe ? selectedRecipe.imagePath : null" />
      </div>
    </md-dialog>
    <md-dialog class="cart" :md-active.sync="cartShowed">
      <md-dialog-title>Liste de course</md-dialog-title>
      <md-dialog-content>
        <h3>Recettes</h3>
        <ul>
          <li v-for="recipe in form.selectedRecipes" v-bind:key="recipe.id">
            {{ recipe.name }}
          </li>
        </ul>
        <div v-if="form.ingredientsCart.length > 0">
          <h3>Ingrédients</h3>
          <ul>
            <li v-for="ingredient in form.ingredientsCart" v-bind:key="ingredient.id">
              {{ ingredient.toString() }}
            </li>
          </ul>
        </div>
      </md-dialog-content>
      <md-dialog-actions>
        <md-button class="md-icon-button md-primary" @click="cartShowed = false"><md-icon>clear</md-icon></md-button>
      </md-dialog-actions>
    </md-dialog>
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
    MdAutocomplete, MdMenu, MdHighlightText, MdList, MdDialog, MdCheckbox, MdDrawer
  } from 'vue-material/dist/components';
  import NoSleep from 'nosleep.js';
  import Multiselect from 'vue-multiselect'
  import tools from '../functions';

  import 'vue-material/dist/vue-material.min.css';
  import 'vue-material/dist/theme/default-dark.css';
  import "vue-multiselect/dist/vue-multiselect.min.css";
  import axios from "axios";
  import VueMobileDetection from 'vue-mobile-detection';

  const noSleep = new NoSleep();
  noSleep.enable();

  Vue.use(VueMobileDetection);

  Vue.use(MdApp);
  Vue.use(MdToolbar);
  Vue.use(MdContent);
  Vue.use(MdCard);
  Vue.use(MdButton);
  Vue.use(MdIcon);
  Vue.use(MdField);
  Vue.use(MdCheckbox);
  Vue.use(MdMenu);
  Vue.use(MdList);
  Vue.use(MdDialog);
  Vue.use(MdDrawer);

  Vue.config.errorHandler = (err, vm, info) => {
    if (process.env.NODE_ENV !== 'production') {
      // Show any error but this one
      if (err.message !== "Cannot read property 'badInput' of undefined") {
        console.error(err);
      }
    }
  }

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
              && tools.normalize(recipe.name).search(tools.normalize(this.form.search)) > -1
              && (this.form.type === '' || this.form.type === 'sweet' && recipe.sweet || this.form.type === 'salty' && recipe.salty || this.form.type === 'none' && !recipe.sweet && !recipe.salty)
              && (this.form.diet === '' || this.form.diet === 'vege' && recipe.vege || this.form.diet === 'vegan' && recipe.vegan || this.form.diet === 'meat' && recipe.meat)
          ) {
            recipes.push(recipe);
          }
        });

        return recipes;
      },
      minToHour(min) {
        return tools.minToHour(min, true);
      },
      showModal(recipe) {
        this.selectedRecipe = recipe;
        this.lightboxShowed = true;
      },
      goToShopping() {
        this.form.ingredientsCart = [];
        const ingredientIds = [];
        this.form.selectedRecipes.forEach(recipe => {
          recipe.recipeIngredients.forEach(recipeIngredient => {

            if (recipeIngredient.ingredient.isRecipe) {
              this.recipeByNames[recipeIngredient.ingredient.name].recipeIngredients.forEach(recipeIngredientInRecipe => {
                this.addIngredientToCart(ingredientIds, recipeIngredientInRecipe, recipeIngredient.quantity ? recipeIngredient.quantity : 1);
              });
            } else {
              this.addIngredientToCart(ingredientIds, recipeIngredient);
            }
          });
        });
        this.form.ingredientsCart.sort((ingredientA, ingredientB) => {
          return ingredientA.typeIndex - ingredientB.typeIndex;
        });

        const ingredientsCartWithKitchen = [];
        this.form.ingredientsCart.forEach((ingredient, index) => {
          if (typeof this.kitchenIngredientsById[ingredient.id] !== 'undefined') {
            const kitchenIngredient = this.kitchenIngredientsById[ingredient.id];
            const kitchenQuantity = this.removeUnit(kitchenIngredient);

            if (!kitchenIngredient.unit && !kitchenIngredient.measure && !kitchenIngredient.quantity) {

            } else if (kitchenIngredient.unit && ingredient.quantities.unit) {
              if (ingredient.quantities.unit > kitchenQuantity) {
                ingredient.quantities.unit -= kitchenQuantity;
                ingredientsCartWithKitchen.push(ingredient);
              }
            } else if (kitchenIngredient.measure) {
              if (typeof ingredient.quantities.measures[kitchenIngredient.measure] !== 'undefined' && ingredient.quantities.measures[kitchenIngredient.measure] > kitchenQuantity) {
                ingredient.quantities.measures[kitchenIngredient.measure] -= kitchenQuantity;
                ingredientsCartWithKitchen.push(ingredient);
              } else if (typeof ingredient.quantities.measures[kitchenIngredient.measure] === 'undefined') {
                ingredientsCartWithKitchen.push(ingredient);
              }
            } else if (!kitchenIngredient.unit && !kitchenIngredient.measure && ingredient.quantities.count) {
              if (ingredient.quantities.count > kitchenQuantity) {
                ingredient.quantities.count -= kitchenQuantity;
                ingredientsCartWithKitchen.push(ingredient);
              }
            } else {
              ingredientsCartWithKitchen.push(ingredient);
            }
          } else {
            ingredientsCartWithKitchen.push(ingredient);
          }
        });
        this.form.ingredientsCart = ingredientsCartWithKitchen;

        this.cartShowed = true;
      },
      removeUnit(recipeIngredient, multiplier = 1) {
        let quantity = recipeIngredient.quantity * multiplier;
        if (recipeIngredient.unit === 'c-à-c' || recipeIngredient.unit === 'c-à-s') {
          quantity *= (recipeIngredient.unit === 'c-à-s' ? 15 : 5);
        }
        if (recipeIngredient.unit === 'g' || recipeIngredient.unit === 'kg') {
          switch(recipeIngredient.unit) {
            case 'kg':
              quantity *= 1000;
              break;
          }
        }
        else if (recipeIngredient.unit === 'l' || recipeIngredient.unit === 'cl'|| recipeIngredient.unit === 'ml') {
          switch(recipeIngredient.unit) {
            case 'l':
              quantity *= 1000;
              break;
            case 'cl':
              quantity *= 10;
              break;
          }
        }
        return quantity;
      },
      addIngredientToCart(ingredientIds, recipeIngredient, multiplier = 1) {
        const ingredientIndex = ingredientIds.indexOf(recipeIngredient.ingredient.id);
        const quantity = this.removeUnit(recipeIngredient, multiplier);

        if (ingredientIndex > -1) {
          if (recipeIngredient.measure) {
            if (typeof this.form.ingredientsCart[ingredientIndex].quantities.measures[recipeIngredient.measure] != 'undefined') {
              this.form.ingredientsCart[ingredientIndex].quantities.measures[recipeIngredient.measure] += quantity;
            } else {
              this.form.ingredientsCart[ingredientIndex].quantities.measures[recipeIngredient.measure] = quantity;
            }
          } else {
            if (recipeIngredient.unit !== null) {
              this.form.ingredientsCart[ingredientIndex].quantities.unit += quantity;
            } else {
              this.form.ingredientsCart[ingredientIndex].quantities.count += quantity;
            }
          }
        } else {
          const ingredientCart = {
            id: recipeIngredient.ingredient.id,
            name : recipeIngredient.ingredient.name,
            typeIndex : recipeIngredient.ingredient.typeIndex,
            quantities : {
              unit: 0,
              count: 0,
              measures : {}
            },
            toString() {
              const quantities = [];
              if (this.quantities.unit) {
                let isLiquid = recipeIngredient.ingredient.isLiquid || recipeIngredient.unit === 'cl' || recipeIngredient.unit === 'l';
                quantities.push(tools.prettyNumber(this.quantities.unit, isLiquid));
              }
              if (this.quantities.count) {
                quantities.push(this.quantities.count);
              }
              for (let measure in this.quantities.measures) {
                quantities.push(this.quantities.measures[measure] + ' ' + measure + (this.quantities.measures[measure] > 1 ? 's' : ''))
              }
              return this.name + (quantities.length > 0 ? (' (' + quantities.join(' + ') + ')') : '');
            }
          };
          if (recipeIngredient.measure) {
            ingredientCart.quantities.measures[recipeIngredient.measure] = quantity;
          } else {
            if (recipeIngredient.unit) {
              ingredientCart.quantities.unit = quantity;
            } else {
              ingredientCart.quantities.count = quantity;
            }
          }

          ingredientIds.push(recipeIngredient.ingredient.id);
          this.form.ingredientsCart.push(ingredientCart);
        }
      },
      clearForm() {
        this.form.type = this.form.diet = this.form.search = '';
        this.form.selectedRecipes = [];
        this.form.ingredientsCart = [];
        this.form.ingredients = [];
        this.filtersShowed = false;
      },
    },
    data() {
      return {
        form : {
          selectedRecipes: [],
          ingredientsCart: [],
          search: '',
          type: '',
          diet: '',
          ingredients: []
        },
        recipes: [],
        kitchen: [],
        kitchenIngredientsById: {},
        recipeByNames: {},
        ingredients: [],
        ingredientsByTypes: [],
        selectedRecipe: null,
        lightboxShowed: false,
        filtersShowed: false,
        cartShowed: false,
      };
    },
    mounted() {
        axios.get('/kitchen/recipes')
          .then(response => {
            this.recipes = response.data.recipes;
            this.ingredients = response.data.ingredients;
            this.kitchen = response.data.kitchen;

            this.kitchen.forEach(kitchenIngredient => {
              this.kitchenIngredientsById[kitchenIngredient.ingredient.id] = kitchenIngredient;
            })

            this.recipes.forEach(recipe => {
              this.recipeByNames[recipe.name] = recipe;
            });

            const ingredientsByType = {};
            this.ingredients.forEach(ingredient => {
              if (typeof ingredientsByType[ingredient.type] === 'undefined') {
                ingredientsByType[ingredient.type] = {
                  name: ingredient.typeName,
                  index: ingredient.typeIndex,
                  ingredients: []
                }
              }
              ingredientsByType[ingredient.type].ingredients.push({
                id : ingredient.id,
                name : ingredient.name,
              });
            });
            for (let i in ingredientsByType){
              this.ingredientsByTypes.push(ingredientsByType[i]);
            }
            this.ingredientsByTypes = this.ingredientsByTypes.sort((a, b) => {
              return a.index - b.index;
            });
          });
    },
  };
</script>
