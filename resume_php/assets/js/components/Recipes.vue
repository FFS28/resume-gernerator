
<template>
    <div>
      Hello World
    </div>
</template>

<script>
    export default {
        data() {
            return {
                recipes: [],
            };
        },
        mounted() {
            let el = document.querySelector("div[data-recipes]");
            let recipes = [];
            console.log(JSON.parse(el.dataset.recipes));
            JSON.parse(el.dataset.recipes).forEach(recipe => {
              console.log(recipe);
            });
            this.recipes.push.apply(this.recipes, recipes);
        },
        components: {

        }
    };
</script>
