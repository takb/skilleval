<template>
  <v-container >
    <v-row>
      <v-col cols="6">
        <MightyComboBox label="Level" v-model="level" :parent="1" @message="showMessage($event)" availableKey="lvl" multiple :disabled="loading" />
      </v-col>
      <v-col cols="6">
        <MightyComboBox label="Category" v-model="category" :parent="2" @message="showMessage($event)" availableKey="cat" multiple :disabled="loading" autoselect/>
      </v-col>
    </v-row>
    <LabeledBlock label="Courses">
      <CatLvlTable :category.sync="category" :level.sync="level">
        <template v-slot:cell="{ cell }">
          <div v-if="cell.text == undefined && Array.isArray(items[cell.key])" style="vertical-align: top;">
            <v-tooltip top v-for="course in items[cell.key]" :key="course.id">
              <template v-slot:activator="{ on }">
                <v-checkbox v-on="on" v-model="course.selected"  :value="course.value" hide-details :click="fillChartData()">
                  <span slot="label" v-on="on">{{course.text}}</span>
                </v-checkbox>
              </template>
              <span>{{'Points: ' + course.value + ' (' + target[cell.key] + ' required)'}}</span>
            </v-tooltip>
            <!-- {{ progress(cell.key) + " - " + cell.key }} -->
          </div>
          <span v-else>
            {{cell.text}}
          </span>
        </template>
        <template v-slot:no-data>
          select at least one level and one category
        </template>
      </CatLvlTable>
    </LabeledBlock>
    <LabeledBlock label="Progress" v-if="category.length && level.length">
      <CustomChart :chart-data="chartData" :height="80"></CustomChart>
    </LabeledBlock>
    <!-- DEBUG MODE OUTPUT<br /><br />
    {{ items }} <br /><br />
    {{ chartData }} <br /><br /> -->
  </v-container>
</template>

<script>
import MightyComboBox from './MightyComboBox';
import CatLvlTable from './CatLvlTable';
import LabeledBlock from './LabeledBlock';
import CustomChart from './CustomChart.js';
export default {
  name: 'Viewer',
  components: {
    MightyComboBox, CatLvlTable, LabeledBlock, CustomChart
  },
  props: {
    debug: Boolean,
  },
  data: () => ({
    level: [],
    category: [],
    loading: false,
    items: [],
    target: [],
    chartData: null,
    // colorNames: ['CornflowerBlue', 'DarkSeaGreen', 'DarkOrange', 'FireBrick', 'DarkViolet', 'Gold', 'DarkOrchid', 'DarkSlateGrey', 'LightBlue', 'LightSeaGreen', 'MediumPurple'],
  }),
  computed: {
  },
  methods: {
    // sumPoints(block) {
    //   var sum = 0;
    //   for(var k in this.items[block]) {
    //     var currentVal = parseInt(this.items[block][k].selected);
    //     sum += currentVal ? currentVal : 0;
    //   }
    //   return sum;
    // },
    progress(block) {
      var sum = 0;
      for(var k in this.items[block]) {
        var currentVal = parseInt(this.items[block][k].selected);
        sum += currentVal ? currentVal : 0;
      }
      return sum >= this.target[block] ? 100 : (sum / this.target[block] * 100).toFixed(2);
    },
    loadItems() {
      this.loading = true;
      this.items.splice(0);
      this.$backend.getPrefetch()
        .then(response => {
          if (response.data.error) {
            return this.$emit('message', {message: response.data.message});
          }
          this.items = response.data.items;
          this.target = response.data.target;
          // eslint-disable-next-line no-console
          // console.log(this.items);
        })
        .catch(reason => {
          return this.$emit('message', {message: reason});
        })
        .finally(() => {
          this.loading = false;
        });
    },
    fillChartData() {
      if (!this.category || !this.level) return;
      var labels = [];
      this.level.forEach(l=>{
        if (l.children.length) {
          l.children.forEach(sl=>{
            labels.push(l.text+' - '+sl.text);
          });
        } else {
          labels.push(l.text);
        }
      });
      var datasets = [];
      this.category.forEach(c=>{
        if (c.children.length) {
          c.children.forEach(sc=>{
            datasets.push(this.getDataSet(c.text+' - '+sc.text, c.value+'.'+sc.value));
          });
        } else {
          datasets.push(this.getDataSet(c.text, c.value+'.0'));
        }
      });
      this.chartData = {labels, datasets}
    },
    getDataSet(label, key) {
      var data = this.getRowsData(key);
      return {
        label,
        backgroundColor: data.map(val=>{
          return val > 50 ? (
            val > 95 ? "rgba(34, 139, 34, 0.7)" : "rgba(255, 140, 0, 0.7)"
          ) : "rgba(178, 34, 34, 0.7)";
        }),
        // hoverBackgroundColor: this.colorNames[index],
        // borderWidth: 3,
        data,
      };
    },
    getRowsData(cKey) {
      var rows = [];
      this.level.forEach(l=>{
        if (l.children.length) {
          l.children.forEach(sl=>{
            var key = l.value+'.'+sl.value+'.'+cKey;
            rows.push(this.progress(key));
          });
        } else {
          var key = l.value+'.0.'+cKey;
          rows.push(this.progress(key));
        }
      });
      return rows;
    },
    showMessage(event) {
      this.$emit('message', event);
    },
  },
  watch: {
    category() {
      this.fillChartData();
    },
    level() {
      this.fillChartData();
    },
  },
  mounted: function() {
    this.loadItems();
  }
};
</script>
