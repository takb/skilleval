<template>
  <v-container >
    <v-row v-show="!viewmode">
      <v-col cols="6">
        <MightyComboBox label="Level" v-model="level" :parent="1" @message="showMessage($event)" availableKey="lvl" multiple :disabled="loading" :autoselectById="viewmode" />
      </v-col>
      <v-col cols="6">
        <MightyComboBox label="Category" v-model="category" :parent="2" @message="showMessage($event)" availableKey="cat" multiple :disabled="loading" autoselect/>
      </v-col>
    </v-row>
    <LabeledBlock :label="matrixLabel">
      <CatLvlTable :category.sync="category" :level.sync="level" :noDataLabel="noDataLabel" :levelLabel="levelLabel" :sublevelLabel="sublevelLabel">
        <template v-slot:cell="{ cell }">
          <div v-if="cell.text == undefined && Array.isArray(items[cell.key])" style="vertical-align: top;">
            <v-tooltip top v-for="course in items[cell.key]" :key="course.id">
              <template v-slot:activator="{ on }">
                <v-checkbox v-on="on" v-model="course.selected" :value="course.value" hide-details :click="fillChartData()" style="margin-bottom: 14px;">
                  <span slot="label" v-on="on">
                    {{course.text}}
                  </span>
                  <span slot="prepend">
                    <a :href="course.url" v-if="course.url" target="_blank"><v-icon>mdi-file-move-outline</v-icon></a>
                  </span>
                </v-checkbox>
              </template>
              <span>{{course.comment ? course.comment + ' - ' : ''}}{{'Points: ' + course.value + ' (' + target[cell.key] + ' required)'}}</span>
            </v-tooltip>
          </div>
          <span v-else>
            {{cell.text}}
          </span>
        </template>
        <template v-slot:no-data>
          {{noLevelLabel}}
        </template>
      </CatLvlTable>
    </LabeledBlock>
    <LabeledBlock :label="progressLabel" v-if="category.length && level.length">
      <CustomChart :chart-data="chartData" :height="80" :stacked="stackedMode" style="margin: 10px;"></CustomChart>
    </LabeledBlock>
    <!-- DEBUG MODE OUTPUT<br /><br />
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
    viewmode: String,
    noDataLabel: String,
    levelLabel: String,
    sublevelLabel: String,
    stackedMode: {
      type: Boolean,
      default: true,
    },
    matrixLabel: {
      type: String,
      default: 'Courses'
    },
    progressLabel: {
      type: String,
      default: 'Progress'
    },
    noLevelLabel: {
      type: String,
      default: 'select at least one level and one category'
    },
  },
  data: () => ({
    level: [],
    category: [],
    loading: false,
    items: [],
    target: [],
    chartData: null,
    chartData2: null,
    hoverBackgroundColors:["rgba(255, 99, 132, 0.8)","rgba(255, 205, 86, 0.8)","rgba(75, 192, 192, 0.8)","rgba(54, 162, 235, 0.8)","rgba(153, 102, 255, 0.8)","rgba(201, 203, 207, 0.8)"],
    backgroundColors:["rgba(255, 99, 132, 0.4)","rgba(255, 205, 86, 0.4)","rgba(75, 192, 192, 0.4)","rgba(54, 162, 235, 0.4)","rgba(153, 102, 255, 0.4)","rgba(201, 203, 207, 0.4)"],
    borderColors:["rgb(255, 99, 132)","rgb(255, 205, 86)","rgb(75, 192, 192)","rgb(54, 162, 235)","rgb(153, 102, 255)","rgb(201, 203, 207)"],
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
      if (this.stackedMode) {
        var catLabels = [];
        var categories = [];
        this.category.forEach(c=>{
          if (c.children.length) {
            c.children.forEach(sc=>{
              categories.push({label: c.text+' - '+sc.text, key:c.value+'.'+sc.value});
            });
          } else {
            categories.push({label: c.text, key:c.value+'.0'});
          }
          catLabels.push(c.text);
        });
        var data = [];
        this.level.forEach((l, i)=>{
          var levelData = [];
          categories.forEach(c => {
            var points = 0;
            if (l.children.length) {
              l.children.forEach(sl=>{
                points += this.progressLevelSum(l.value+'.'+sl.value+'.'+c.key);
              });
            } else {
                points += this.progressLevelSum(l.value+'.0.'+c.key)
            }
            levelData.push(points);
          });
          data.push({
            label: l.text,
            data: levelData,
            borderWidth: 2,
            backgroundColor: this.backgroundColors[i],
            hoverBackgroundColor: this.hoverBackgroundColors[i],
            borderColor: this.borderColors[i],
          });
        });
        this.chartData = {
          labels: catLabels,
          datasets: data
        };
      } else {
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
      }
    },
    progressLevelSum(block) {
      var sum = 0;
      for(var k in this.items[block]) {
        var currentVal = parseInt(this.items[block][k].selected);
        sum += currentVal ? currentVal : 0;
      }
      return sum;
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
    progress(block) {
      var sum = 0;
      for(var k in this.items[block]) {
        var currentVal = parseInt(this.items[block][k].selected);
        sum += currentVal ? currentVal : 0;
      }
      return sum >= this.target[block] ? 100 : (sum / this.target[block] * 100).toFixed(2);
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
