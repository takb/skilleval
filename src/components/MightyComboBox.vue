<template>
  <div>
    <v-combobox ref="combo" outlined dense clearable hide-selected background-color="#fff"
      :chips="multiple" :deletable-chips="multiple" :multiple="multiple" :items="items" :label="label" :loading="compLoading" :disabled="compLoading || disabled || (loadFromKey && (!items || items.length == 0))"
      :value="value" :search-input.sync="search" @input="addItem($event)" @click:clear="$emit('input', $event)" @change="sortItems($event)">
    <template v-if="noData && editable" v-slot:no-data>
      <v-list-item>
        <v-list-item-content>
          <v-list-item-title>
            No results matching "<strong>{{ search }}</strong>".
            Press <kbd>enter</kbd> to create a new one
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </template>
    <template v-slot:item="{ index, item }">
      <v-text-field v-if="editing === item" v-model="editing.text" autofocus flat background-color="transparent" hide-details solo
        @keydown.enter.stop.prevent="editItem(index, item)" />
      <span v-else> {{ item.text }} <span v-if="showId">({{ item.value }})</span></span>
      <v-spacer></v-spacer>
      <v-list-item-action @click.stop v-if="editable">
        <v-btn icon @click.stop.prevent="editItem(index, item)" >
          <v-icon>{{ editing !== item ? 'mdi-pencil' : 'mdi-check' }}</v-icon>
        </v-btn>
      </v-list-item-action>
      <v-list-item-action @click.stop v-if="editable">
        <v-btn icon @click.stop.prevent="deleteItem(item)" >
          <v-icon>mdi-delete</v-icon>
        </v-btn>
      </v-list-item-action>
    </template>
    </v-combobox>
  </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  name: 'MightyComboBox',
  props: {
    label: String,
    value: [String, Object, Array],
    parent: Number,
    parentObj: [String, Object],
    parentAvailableKey: String,
    multiple: Boolean,
    disabled: Boolean,
    availableKey: String,
    loadFromKey: Boolean,
    readonly: Boolean,
    loading: Boolean,
    showId: Boolean,
    autoselect: Boolean,
    autoselectById: String,
  },
  data: () => ({
    items: [],
    search: null,
    editing: null,
    index: -1,
    valueBeforeEdit: null,
    loadingCmp: false,
  }),
  methods: {
    editItem(index, item) {
      if (!this.editing) {
        this.editing = item;
        this.index = index;
        this.valueBeforeEdit = item.text;
      } else {
        if (this.valueBeforeEdit == item.text) {
          this.editing = null;
          this.index = -1;
          return;
        }
        var $this = this;
        this.$backend.renElement(item.value, item.text)
          .then(response => {
            if (response.data.error || !response.data.success) {
              return $this.$emit('message', {message: response.data.message});
            }
            $this.editing = null;
            $this.index = -1;
            if (typeof this.parentObj == 'object') {
              this.parentObj.children = this.parentObj.children.filter(e => {
                if (e.value == item.value) {
                  e.text = item.text;
                }
                return true;
              });
              if ($this.parentAvailableKey) {
                $this.$store.commit('updateAvailable', {key: $this.parentAvailableKey, value: $this.parentObj});
              }
            }
            if ($this.availableKey) {
              $this.$store.commit('updateAvailable', {key: $this.availableKey, value: item});
            }
            this.$emit('changed', '');
            $this.loadItems();
          }).catch(reason => {
            return $this.$emit('message', {message: reason});
          });
      }
    },
    addItem(e) {
      this.$emit('input', '');
      if (typeof e === "string" && e.length > 0 && !this.multiple) {
        var $this = this;
        this.$backend.addElement(typeof this.parentObj == 'object' ? this.parentObj.value : this.parent, e)
          .then(response => {
            if (response.data.error || !response.data.item) {
              return $this.$emit('message', {message: response.data.message, color: 'warning'});
            }
            e = response.data.item;
            $this.items.push(e);
            if ($this.availableKey) {
              $this.$store.commit('setAvailable', {key: $this.availableKey, value: [e]});
            }
            // eslint-disable-next-line no-console
            // console.log($this.$store.state.available[$this.availableKey]);
            if (typeof this.parentObj == 'object') {
              this.parentObj.children.push(e);
              if ($this.parentAvailableKey) {
                $this.$store.commit('updateAvailable', {key: $this.parentAvailableKey, value: $this.parentObj});
              }
            }
            this.$refs["combo"].isFocused = false;
            this.$emit('changed', '');
            return this.$emit('input', e);
          }).catch(reason => {
            return $this.$emit('message', {message: reason});
          });
      } else {
        if (e == null) e = ''; // do not set external value to null!
        if (this.multiple) {
            e = e.filter(el => {return typeof el === 'object'}); // do not allow manual input in multiple mode
        }
        this.$emit('input', e);
      }
    },
    deleteItem(item) {
      var $this = this;
      this.$backend.delElement(item.value)
        .then(response => {
          if (response.data.error || !response.data.success) {
            return $this.$emit('message', {message: response.data.message});
          }
          if (typeof this.parentObj == 'object') {
            this.parentObj.children = this.parentObj.children.filter(e => {return e.value != item.value;});
            if ($this.parentAvailableKey) {
              $this.$store.commit('updateAvailable', {key: $this.parentAvailableKey, value: $this.parentObj});
            }
          }
          if ($this.availableKey) {
            $this.$store.commit('delAvailable', {key: $this.availableKey, value: item});
          }
          this.$emit('changed', '');
          $this.loadItems();
        }).catch(reason => {
          return $this.$emit('message', {message: reason});
        });
    },
    loadItems() {
      this.$emit('input', '');
      this.items.splice(0);
      this.loadingCmp = true;
      if (!this.loadFromKey) {
        var $this = this;
        this.$backend.getElements(typeof this.parentObj == 'object' ? this.parentObj.value : this.parent)
          .then(response => {
            // eslint-disable-next-line no-console
            // console.log(this.parentObj);
            if (response.data.error) {
              return $this.$emit('message', {message: response.data.message});
            }
            $this.items.push.apply($this.items, response.data.items);
            if (this.autoselect) {
              this.$emit('input', $this.items);
            }
            if (this.autoselectById && typeof this.autoselectById == 'string') {
              var input = [];
              var ids = this.autoselectById.split(',');
              if (ids.length > 0) {
                ids.forEach(id => {
                  this.items.forEach(e => {
                    if (id == e.value.toString()) {
                      input.push(e);
                    }
                  });
                });
                this.$emit('input', input);
              }
            }
          })
          .catch(reason => {
            return $this.$emit('message', {message: reason});
          })
          .finally(() => {
            $this.loadingCmp = false;
          });
      } else {
        this.items = this.available[this.availableKey];
        this.loadingCmp = false;
        this.$emit('input', []);
      }
    },
    sortItems(e){
      if (Array.isArray(e)) {
        e.sort((a,b) => {return a.value - b.value});
      }
    },
  },
  computed: {
    noData() {
      return (typeof this.value !== 'object');
    },
    editable() {
      return !this.multiple && this.writePermission;
    },
    compLoading() {
      return this.loading || this.loadingCmp;
    },
    ...mapState(['writePermission', 'available']),
  },
  watch: {
    parent() {
      if (this.parent)
        this.loadItems();
    },
    parentObj() {
      if (this.parentObj && this.parentObj.value)
        this.loadItems();
    },
    available() {
      if (this.loadFromKey && this.availableKey) {
        this.items = this.available[this.availableKey];
      }
    }
  },
  mounted: function() {
    var $this = this;
    this.$refs['combo'].$refs['input'].onkeydown = (e) => {
      if (e.key === 'Delete' || e.key === 'Backspace' || e.keyCode == 8 || e.keyCode == 46) {
        $this.$emit('input', '');
        e.stopPropagation();
      }
    };
    if (this.availableKey && !this.loadFromKey) {
      this.$store.commit('setAvailable', {key: this.availableKey, value: this.items});
    }
    this.loadItems();
  }
};
</script>
