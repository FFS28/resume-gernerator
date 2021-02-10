<template>
  <div class="page-container">
    <md-app md-waterfall md-mode="fixed">
      <md-app-toolbar>
        <div class="md-toolbar-row">
          <div class="md-toolbar-section-start">
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

          <multiselect class="field-ingredients" v-model="form.ingredients" :options="ingredientsByTypes" :multiple="true"
                       placeholder="Ingredients"
                       selectLabel="Selection de l'ingrédient"
                       deselectLabel="Supprimer l'ingrédient"
                       selectedLabel="Selectionné"
                       track-by="name" label="name"
                       group-label="name" group-values="ingredients" :group-select="false">
              <span slot="noResult">Aucun ingrédient trouvé</span>
          </multiselect>

          <div class="md-toolbar-section-end">
            <md-button class="md-raised md-icon-button" v-on:click="clearForm()">
              <md-icon>clear</md-icon>
            </md-button>
            <md-button class="md-primary md-raised md-icon-button" v-on:click="goToShopping()" :disabled="form.selectedRecipes.length === 0">
              <md-icon>shopping_cart</md-icon>
            </md-button>
          </div>
        </div>
      </md-app-toolbar>
      <md-app-content class="recipes">
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
                  <md-button class="md-fab" :href="'/kitchen/' + recipe.slug" v-bind:class="{type_sugar: recipe.sweet, type_salt: recipe.salty, 'bg_black': !recipe.sweet && !recipe.salty }">
                    <md-icon>local_dining</md-icon>
                  </md-button>
                </div>
              </md-card-actions>
            </md-card-expand>
          </div>
        </md-card>
      </md-app-content>
    </md-app>
    <md-dialog :md-active.sync="lightboxShowed">
      <div class="center-cropped"
           v-bind:style="{'background-image': 'url(\'' + (selectedRecipe ? selectedRecipe.imagePath : '') + '\')'}">
        <img :src="selectedRecipe ? selectedRecipe.imagePath : null" />
      </div>
    </md-dialog>
    <md-dialog class="cart" :md-active.sync="cartShowed">
      <md-dialog-title>Liste de course</md-dialog-title>
      <md-card>
        <md-card-content>
          <ul>
            <li v-for="ingredient in form.ingredientsCart" v-bind:key="ingredient.id">
              {{ ingredient.toString() }}
            </li>
          </ul>
        </md-card-content>
      </md-card>
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
    MdAutocomplete, MdMenu, MdHighlightText, MdList, MdDialog, MdCheckbox
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
  Vue.use(MdCheckbox);
  Vue.use(MdMenu);
  Vue.use(MdList);

  Vue.use(MdDialog);

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

        recipes.sort((recipeA, recipeB) => {
          let sortA = 1;
          let sortB = 1;

          if (recipeA.salty) sortA = 3;
          if (recipeB.salty) sortB = 3;
          if (recipeA.sweet) sortA = 2;
          if (recipeB.sweet) sortB = 2;

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
              this.recipeByNames[recipeIngredient.ingredient.name].recipeIngredients.forEach(recipeIngredient => {
                this.addIngredientToCart(ingredientIds, recipeIngredient);
              });
            } else {
              this.addIngredientToCart(ingredientIds, recipeIngredient);
            }
          });
        });
        this.form.ingredientsCart.sort((ingredientA, ingredientB) => {
          return ingredientA.typeIndex - ingredientB.typeIndex;
        });
        this.cartShowed = true;
      },
      addIngredientToCart(ingredientIds, recipeIngredient) {
        const ingredientIndex = ingredientIds.indexOf(recipeIngredient.ingredient.id);
        let quantity = recipeIngredient.quantity;
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
                quantities.push(tools.prettyNumber(this.quantities.unit, recipeIngredient.ingredient.isLiquid));
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
            console.log(recipeIngredient, quantity, ingredientCart);
          } else {
            if (recipeIngredient.unit !== null) {
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
        recipeByNames: {},
        ingredients: [],
        ingredientsByTypes: [],
        selectedRecipe: null,
        lightboxShowed: false,
        cartShowed: false,
      };
    },
    mounted() {
      let elRecipes = document.querySelector("div[data-recipes]");
      let elIngredients = document.querySelector("div[data-ingredients]");
      this.recipes = JSON.parse(elRecipes.dataset.recipes);
      this.ingredients = JSON.parse(elIngredients.dataset.ingredients);

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
      })
      console.log(this.ingredientsByTypes);
    },
  };
</script>
