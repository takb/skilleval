import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify';
import store from './store'
import backend from './backend';
Vue.config.productionTip = false;
backend.store = store;
Vue.prototype.$backend = backend;
new Vue({
  vuetify,
  store,
  render: h => h(App)
}).$mount('#app')
