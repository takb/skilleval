import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify';
import store from './store'
import backend from './backend';
Vue.config.productionTip = false;
backend.store = store;
Vue.prototype.$backend = backend;
const props = {};
const propsNames = ['viewmode', 'noDataLabel', 'levelLabel', 'sublevelLabel', 'matrixLabel', 'progressLabel', 'noLevelLabel' ];
if (document.getElementById('app') && document.getElementById('app').attributes) {
  propsNames.forEach(key => {
    props[key] = document.getElementById('app').attributes[key] && document.getElementById('app').attributes[key].value;
  });
}
// eslint-disable-next-line no-console
console.log(props);

new Vue({
  vuetify,
  store,
  render: h => h(App, {
    props: props
  })
}).$mount('#app')
