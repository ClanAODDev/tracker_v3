// import Chart from 'chart.js';
import Vue from "vue";
import VueResource from "vue-resource";

Vue.use(VueResource);

export default Vue.extend({
    template: `
        <div>
            <canvas v-el:canvas></canvas>
        </div>
    `,

    props: ['url'],

    ready() {
        this.$http.get(this.url)
            .then(response => {
                const data = response.data;
                this.render(data, {
                    animationEasing: "easeInOutQuint",
                    animationSteps: 75,
                    percentageInnerCutout: 50,
                    animateScale: true,
                    responsive: true
                });
            });
    },

    methods: {
        render(data, options) {
            const chart = new Chart(
                this.$els.canvas.getContext('2d')
            ).Pie(data, options);
        }
    }
});
