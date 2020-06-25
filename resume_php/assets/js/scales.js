import Vue from 'vue';
import Scales from './components/Scales';
import '../css/scales.scss';

new Vue({
    el: '#scales',
    render: h => h(Scales)
});