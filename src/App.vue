<template>
  <v-app>
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-sheet>
            <v-toolbar class="teal">
              <v-toolbar-title>SkillEval</v-toolbar-title>
              <v-spacer/>
              <!-- <v-btn icon>
                <v-icon large>mdi-help-circle-outline</v-icon>
              </v-btn> -->
              <v-btn icon v-if="writePermission" @click="editMode = !editMode;">
                <v-icon large>mdi-cog-outline</v-icon>
              </v-btn>
            </v-toolbar>
            <Admin v-if="editMode && writePermission"  @message="showMessage($event)"/>
            <Viewer v-else  @message="showMessage($event)"/>
          </v-sheet>
        </v-col>
      </v-row>
    </v-container>
    <v-snackbar vertical  bottom :color="sbColor" v-model="sb">
      {{ sbText }} <v-btn dark text @click="sb = false">Close</v-btn>
    </v-snackbar>
  </v-app>
</template>

<style>
.flip {
  transform: rotate(-180deg);
}
th:hover {
  background: #eeeeee;
}
</style>

<script>
import { mapState } from 'vuex';
import Admin from './components/Admin';
import Viewer from './components/Viewer';
export default {
  name: 'App',
  components: {
    Admin, Viewer
  },
  data: () => ({
    sb: false,
    sbText: "",
    sbColor: "error",
    sbTimeout: 6000,
    editMode: false,
  }),
  computed: {
    ...mapState(['loaded', 'readPermission', 'writePermission', 'available']),
  },
  methods: {
    checkStatus() {
      var $this = this;
      this.$backend.getStatus()
        .catch(reason => {
          $this.showMessage(reason);
        });
    },
    showMessage(event) {
      this.sbText = event.message;
      this.sbColor = event.color || "error";
      this.sb = true;
    },
  },
  created: function() {
    this.checkStatus();
  }
};
</script>
