<template>
  <div>
  <v-data-table class="dt" :headers="dtHeader" :items="dtData" :items-per-page="1000" :loading="dtLoading" hide-default-footer hide-default-header>
    <template v-slot:header="row">
      <tr v-if="dtData.length">
        <th v-for="(g, i) in dtHeaderGroups" :colspan="g.value || 1" :key="i" style="text-align: center;">
          {{ g.text }}
        </th>
      </tr>
      <tr class="v-data-table-header" v-if="dtData.length">
        <th v-for="(g, i) in row.props.headers" :key="i" style="border-bottom: 1px solid #ddd;">
          {{ g.text }}
        </th>
      </tr>
    </template>
    <template v-slot:item="row">
      <tr>
        <td v-for="(col, name) in itemFilter(row.item)" :key="row.index + '.' + name">
          <slot name="cell" :cell="{key: row.item.key+'.'+name, text: name == 'lname' || name == 'slname' ? col : undefined }"></slot>
        </td>
      </tr>
    </template>
    <template v-slot:no-data>
      <center style="font-size: 1.3em;">
        <slot name="no-data">No data available</slot>
      </center>
    </template>
  </v-data-table>
</div>
</template>

<style scoped>
.dt {
  white-space: nowrap;
}
</style>

<script>
import { mapState } from 'vuex';
export default {
  name: 'CatLvlTable',
  props: {
    debug: Boolean,
    category: [Array, String],
    level: [Array, String],
    rebuild: Boolean,
    fold: Boolean,
  },
  data: () => ({
    dtLoading: false,
    dtHeaderGroups: [],
    dtHeader: [],
    dtData: [],
  }),
  computed: {
    ...mapState(['available']),
  },
  methods: {
    buildTable(){
      this.dtData.splice(0);
      // eslint-disable-next-line no-console
      // console.log(this.category, typeof this.category);
      if (!Array.isArray(this.category) || !Array.isArray(this.level) || !this.category.length || !this.level.length) {
        return;
      }
      this.dtLoading = true;
      // headers
      this.dtHeaderGroups = [{text: ''}, {text: ''}];
      this.dtHeader = [{text: 'Level', value: 'lname'}, {text: 'Condition', value: 'slname'}];
      this.category.forEach(c => {
        this.dtHeaderGroups.push({text: c.text, value: c.children.length})
        if (c.children.length) {
          c.children.forEach(s => {
            this.dtHeader.push({text: s.text, value: s.value})
          });
        } else {
          this.dtHeader.push({text: "", value: 0})
        }
      });
      this.level.forEach(l => {
        if (l.children.length) {
          var firstchild = true;
          l.children.forEach(sl => {
            var row = {key: l.value+'.'+sl.value, lname: firstchild ? l.text : '', slname: sl.text};
            this.category.forEach(c => {
              if (c.children.length > 1) {
                c.children.forEach(sc => {
                  row[c.value+'.'+sc.value] = 'data '+l.value+'.'+sl.value+'.'+c.value+'.'+sc.value;
                });
              } else {
                row[c.value+'.0'] = 'data '+l.value+'.'+sl.value+'.'+c.value+'.0';
              }
            });
            this.dtData.push(row);
            firstchild = false;
          });
        } else {
          var row = {key: l.value+'.0', lname: l.text, slname: ''};
          this.category.forEach(c => {
            if (c.children.length) {
              c.children.forEach(sc => {
                row[c.value+'.'+sc.value] = 'data '+l.value+'.0.'+c.value+'.'+sc.value;
              });
            } else {
              row[c.value+'.0'] = 'data '+l.value+'.0.'+c.value+'.0';
            }
          });
          this.dtData.push(row);
        }
      });
      this.dtLoading = false;
    },
    itemFilter(obj){
      var ret = {};
      for(var k in obj) {
        if (k != 'key')
          ret[k] = obj[k];
      }
      return ret;
    },
    showMessage(event) {
      this.$emit('message', event);
    },
  },
  watch: {
    category() {
      this.buildTable();
    },
    level() {
      this.buildTable();
    },
    rebuild() {
      this.buildTable();
    }
  },
  mounted() {
    this.buildTable();
  }
};
</script>
