
<template>
    <ul>
        <li v-for="scaleGroup in scales">
            <h1>{{ scaleGroup.title }}</h1>
            <ul>
                <li v-for="scale in scaleGroup.children">
                    <VueSlideBar v-model="scale.value" :data="scale.data" :range="scale.labels"
                                 :min="0" :max="100" :showTooltip="true" :lineHeight="10"
                                 :labelStyles="{ color: 'red', backgroundColor: '#4a4a4a' }"
                                 :processStyle="{ backgroundColor: 'red' }"
                                 :tooltipStyles="{ backgroundColor: 'red', borderColor: 'red' }"
                    />
                </li>
            </ul>
        </li>
    </ul>
</template>

<script>
    import VueSlideBar from 'vue-slide-bar';

    export default {
        data() {
            return {
                scales: [],
            };
        },
        mounted() {
            let el = document.querySelector("div[data-scales]");
            let scales = [];
            JSON.parse(el.dataset.scales).forEach(scaleGroup => {
                const _scaleGroup = {
                    title: scaleGroup.title,
                    children: []
                };
                scaleGroup.children.forEach(scale => {
                    let scaleData = [];
                    let scaleLabels = [];
                    for(let i = 0; i <= 100; i+=10) {
                        scaleData.push(i);
                    }
                    scale.labels.forEach(label => {
                        scaleLabels.push({'label': label})
                    });

                    _scaleGroup.children.push(
                        {
                            title: scale.title,
                            description: scale.description,
                            value: scale.value,
                            labels: scaleLabels,
                            data: scaleData,
                        }
                    );
                });
                scales.push(_scaleGroup);
            });
            this.scales.push.apply(this.scales, scales);
        },
        components: {
            VueSlideBar
        }
    };
</script>