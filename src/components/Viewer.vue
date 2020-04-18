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
    <LabeledBlock label="Block assignment">
      <CatLvlTable :category.sync="category" :level.sync="level">
        <template v-slot:cell="{ cell }">
          <div v-if="cell.text == undefined && Array.isArray(items[cell.key])">
            <v-checkbox v-for="course in items[cell.key]" :key="course.id"
              v-model="course.selected" :label="course.text" :value="course.value" hide-details
            />
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
    {{ items }} <br /><br />
  </v-container>
</template>

<script>
import MightyComboBox from './MightyComboBox';
import CatLvlTable from './CatLvlTable';
import LabeledBlock from './LabeledBlock'
export default {
  name: 'Viewer',
  components: {
    MightyComboBox, CatLvlTable, LabeledBlock
  },
  props: {
    debug: Boolean,
  },
  data: () => ({
    level: [],
    category: [],
    loading: false,
    items: [],
  }),
  computed: {
  },
  methods: {
    loadItems() {
      this.loading = true;
      this.items.splice(0);
      this.$backend.getPrefetch()
        .then(response => {
          if (response.data.error) {
            return this.$emit('message', {message: response.data.message});
          }
          this.items = response.data.items;
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
    showMessage(event) {
      this.$emit('message', event);
    },
  },
  mounted: function() {
    this.loadItems();
  }
};
</script>
