import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    loaded: false,
    writePermission: false,
    readPermission: false,
    available: {},
  },
  mutations: {
    setWP(state, val) {
      state.writePermission = val;
    },
    setRP(state, val) {
      state.readPermission = val;
    },
    setLoaded(state) {
      state.load = true;
    },
    setAvailable(state, val) {
      if (state.available[val.key]) {
        state.available[val.key].push(...val.value);
      } else {
        Vue.set(state.available, val.key, val.value);
      }
    },
    delAvailable(state, val) {
      if (state.available[val.key]) {
        state.available[val.key] = state.available[val.key].filter(e => e.value !== val.value.value);
      }
    },
    updateAvailable(state, val) {
      if (state.available[val.key]) {
        state.available[val.key] = state.available[val.key].filter(e => {
          if (e.value == val.value.value) {
            e.text = val.value.text;
          }
          return true;
        });
      }
    },
    resetAvailable(state, val) {
      if (state.available[val.key]) {
        state.available[val.key].splice(0);
      }
    },
  }
})
// eslint-disable-next-line no-console
// console.log('resetAv', val.key, state.available);
