<template>
  <v-app>
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-sheet>
            <v-toolbar class="header" flat>
              <v-toolbar-title>hei4SKILL matrix app</v-toolbar-title>
              <v-spacer/>
              <!-- <v-btn icon>
                <v-icon large>mdi-help-circle-outline</v-icon>
              </v-btn> -->
              <img src="/assets/uni.gif" />
              <v-spacer/>
              <v-btn icon v-if="writePermission" @click="editMode = !editMode;">
                <v-icon large>mdi-cog-outline</v-icon>
              </v-btn>
            </v-toolbar>
            <Admin v-if="editMode && writePermission"  @message="showMessage($event)" @viewmode="setViewmode($event)" />
            <Viewer v-else @message="showMessage($event)" :viewmode.sync="viewmode" :stackedMode="stackedMode" :noDataLabel="noDataLabel" :levelLabel="levelLabel" :sublevelLabel="sublevelLabel" :matrixLabel="matrixLabel" :progressLabel="progressLabel" :noLevelLabel="noLevelLabel" />
          </v-sheet>
          <br /><br /><br /><br />
        </v-col>
      </v-row>
    </v-container>
    <v-footer fixed class="transparent py-0">
      <v-container class="py-0 my-0">
        <v-row class="py-0 my-0">
          <v-col cols="12" class="py-0 my-0">
            <v-card flat tile width="100%">
              <v-card-text class="grey text-center">
              </v-card-text>
              <v-card-text class="white--text grey darken-1 text-center">
                &copy; {{ new Date().getFullYear() }} — <strong>Zentrales Sprachlabor, Universität Heidelberg</strong>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-footer>
    <v-snackbar vertical bottom :color="sbColor" v-model="sb">
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
.header {
  border-bottom: 6px solid;
  border-color: #cc0130 !important;
  margin-bottom: 6px;
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
  props: {
    noDataLabel: String,
    levelLabel: String,
    sublevelLabel: String,
    matrixLabel: String,
    progressLabel: String,
    noLevelLabel: String,
  },
  data: () => ({
    sb: false,
    sbText: "",
    sbColor: "error",
    sbTimeout: 3000,
    editMode: false,
    viewmode: '131,4,5,6,133',
    stackedMode: true,
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
    setViewmode(event) {
      // eslint-disable-next-line no-console
      // console.log(event);
      this.viewmode = event.toString();
      this.stackedMode = event.toString().split(',').length > 1;
      this.editMode = false;
    },
  },
  created: function() {
    this.checkStatus();
  },
};
</script>
