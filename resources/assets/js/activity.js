import Vue from "vue";
import DonutGraph from "./components/DonutGraph";
import DonutGraphWithLegend from "./components/DonutGraphWithLegend";

// TODO: graph for division rank demographic

new Vue({
    el: 'body',

    components: {
        DonutGraph,
        DonutGraphWithLegend
    }
});
