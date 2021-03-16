import Vue from 'vue';
import Recipe from './components/Recipe';
import '../css/recipe.scss';

new Vue({
    el: '#recipe',
    render: h => h(Recipe)
});
