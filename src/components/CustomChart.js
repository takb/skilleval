import { Bar, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
  extends: Bar,
  mixins: [reactiveProp],
  props: {
    stacked: Boolean,
  },
  data: () => ({
    options: {
      barPercentage: 0.95,
      legend: {
        display: false,
      },
      scales: {
        yAxes: [{
          ticks: {
            min: 0,
            max: 100,
            callback: function (value) {
              return (value / 100 * 100).toFixed(0) + '%'; // convert it to percentage
            },
          },
          scaleLabel: {
            display: false,
          },
        }],
      },
    },
    stackedOptions: {
      barPercentage: 0.95,
      legend: {
        display: false,
      },
      scales: {
        xAxes: [{
          stacked: true,
        }],
        yAxes: [{
          stacked: true,
          ticks: {
            min: 0,
            max: 100,
            callback: function (value) {
              return (value / 100 * 100).toFixed(0) + '%'; // convert it to percentage
            },
          },
          scaleLabel: {
            display: false,
          },
        }],
      },
    },
  }),
  mounted () {
    this.renderChart(this.chartData, this.stacked ? this.stackedOptions : this.options)
  }
}
