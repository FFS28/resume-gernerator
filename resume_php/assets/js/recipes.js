import Vue from 'vue';
import Recipes from './components/Recipes';
import '../css/recipes.scss';

new Vue({
    el: '#recipes',
    render: h => h(Recipes)
});
