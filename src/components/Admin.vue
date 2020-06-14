<template>
  <v-container>
    <v-row>
      <v-col cols="6">
        <MightyComboBox label="Level" v-model="level" :parent="1" @message="showMessage($event)" availableKey="lvl" :showId="true" @changed="updateTables()" />
      </v-col>
      <v-col cols="6">
        <MightyComboBox label="Category" v-model="category" :parent="2" @message="showMessage($event)" availableKey="cat" @changed="updateTables()" />
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="6">
        <MightyComboBox label="Sublevel" v-model="sublevel" :disabled="!level" :parentObj="level" parentAvailableKey="lvl" @message="showMessage($event)" @changed="updateTables()" />
      </v-col>
      <v-col cols="6">
        <MightyComboBox label="Subcategory" v-model="subcategory" :disabled="!category" :parentObj="category" parentAvailableKey="cat" @message="showMessage($event)" @changed="updateTables()" />
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <v-expansion-panels>
          <v-expansion-panel>
            <v-expansion-panel-header>
              Target points per block
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <CatLvlTable :category.sync="available['cat']" :level.sync="available['lvl']" :rebuild="rebuildTable"  :loading="pointsLoading">
                <template v-slot:cell="{ cell }">
                  <span v-if="cell.text == undefined">
                    <v-text-field hint="Target points" v-model="points[cell.key]" @keydown.enter="savePoints(cell.key)" @blur="savePoints(cell.key)"/>
                  </span>
                  <span v-else>
                    {{cell.text}}
                  </span>
                </template>
              </CatLvlTable>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <MightyComboBox label="Course" v-model="course" :parent="3" @message="showMessage($event)" />
        <v-card v-if="course" :loading="courseLoading">
          <v-card-title>
            <v-icon large left>mdi-teach </v-icon> {{ '&nbsp;&nbsp;' + course.text }}
            <v-spacer />
            <v-text-field class="margin" outlined placeholder="Points" v-model="course.points" @keydown.enter="saveCourseVal('points')" @blur="saveCourseVal('points')" :disabled="courseLoading" />
            <v-text-field class="margin" outlined placeholder="Comment" v-model="course.comment" @keydown.enter="saveCourseVal('comment')" @blur="saveCourseVal('comment')" :disabled="courseLoading" />
            <v-text-field class="margin" outlined placeholder="URL" v-model="course.url" @keydown.enter="saveCourseVal('url')" @blur="saveCourseVal('url')" :disabled="courseLoading" />
          </v-card-title>
          <div style="position: relative; padding: 12px;">
            <CatLvlTable :category.sync="available['cat']" :level.sync="available['lvl']" :rebuild="rebuildTable" label="Block assignment">
              <template v-slot:cell="{ cell }">
                <span v-if="cell.text == undefined">
                  <v-checkbox :disabled="courseLoading" hint="Assignment to this Level/Category" v-model="course[cell.key]" @change="saveCourseVal(cell.key)"/>
                </span>
                <span v-else>
                  {{cell.text}}
                </span>
              </template>
            </CatLvlTable>
          </div>
        </v-card>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <ul>
          <li ><a @click="setViewmode('131,4,5,6,133');">Default user view for students</a></li>
          <li v-for="level in available['lvl']" :key="level.value"><a @click="setViewmode(level.value);">User view for {{level.text}}</a></li>
        </ul>
      </v-col>
    </v-row>
  </v-container>
</template>

<style>
  .v-messages, .v-text-field__details {
    min-height: 0 !important;
    margin-bottom: 0 !important;
  }
  .margin {
    padding-left: 6px !important;
    padding-right: 6px !important;
  }
</style>

<script>
import { mapState } from 'vuex';
import MightyComboBox from './MightyComboBox';
import CatLvlTable from './CatLvlTable';
export default {
  name: 'Admin',
  components: {
    MightyComboBox, CatLvlTable,
  },
  props: {
    debug: Boolean,
  },
  data: () => ({
    level: "",
    sublevel: "",
    category: "",
    subcategory: "",
    rebuildTable: false,
    pointsLoading: false,
    points: {},
    courseLoading: false,
    course: "",
    assign: {},
  }),
  computed: {
    ...mapState(['available']),
  },
  methods: {
    loadPoints() {
      this.pointsLoading = true;
      this.$backend.loadValues(0)
        .then(response => {
          if (response.data.error) {
            return this.$emit('message', {message: response.data.message});
          }
          for (var key in response.data.values)
            this.$set(this.points, key, response.data.values[key]);
        })
        .catch(reason => {
          return this.$emit('message', {message: reason});
        })
        .finally(() => {
          this.pointsLoading = false;
        });
    },
    savePoints(key) {
      this.$backend.saveValue(0, key, this.points[key])
        .then(response => {
          this.$emit('message', {message: response.data.message, color: response.data.error ? 'error' : 'success'});
        }).catch(reason => {
          this.$emit('message', {message: reason});
        });
    },
    saveCourseVal(key) {
      this.$backend.saveValue(this.course.value, key, this.course[key])
        .then(response => {
          this.$emit('message', {message: response.data.message, color: response.data.error ? 'error' : 'success'});
        }).catch(reason => {
          this.$emit('message', {message: reason});
        });
    },
    loadCourse() {
      this.courseLoading = true;
      this.$backend.loadValues(this.course.value)
        .then(response => {
          if (response.data.error) {
            return this.$emit('message', {message: response.data.message});
          }
          for (var key in response.data.values) {
            this.$set(this.course, key, response.data.values[key] || '');
          }
        })
        .catch(reason => {
          return this.$emit('message', {message: reason});
        })
        .finally(() => {
          this.courseLoading = false;
        });
    },
    showMessage(event) {
      this.$emit('message', event);
    },
    setViewmode(event) {
      this.$emit('viewmode', event);
    },
    updateTables() {
      // eslint-disable-next-line no-console
      // console.log(event);
      this.rebuildTable = !this.rebuildTable;
    },
  },
  watch: {
    rebuildTable() {
      this.loadPoints();
    },
    course() {
      if (!this.course.value) {
        return;
      }
      this.loadCourse();
    },
  },
  mounted() {
    this.loadPoints();
  },
};
</script>
