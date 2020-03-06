export default ({
  http: require('axios').create({
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json',
    },
    withCredentials: true
  }),

  ajaxCall(params) {
    var store = this.store;
    return this.http.post('https://www.genkidelic.de/skilleval/ajax_backend.php', params).then((response) => {
      if (response.data.rp)
        store.commit('setRP', response.data.rp);
      if (response.data.wp)
        store.commit('setWP', response.data.rp);
      return response;
    });
  },

  getStatus() {
    return this.ajaxCall({action: "status"})
  },

  getElements(parent) {
    return this.ajaxCall({action: "load", parent})
  },

  addElement(parent, text) {
    return this.ajaxCall({action: "add", parent, text})
  },

  delElement(id) {
    return this.ajaxCall({action: "del", id})
  },

  renElement(id, text) {
    return this.ajaxCall({action: "ren", id, text})
  },

  loadValues(id) {
    return this.ajaxCall({action: "loadval", id})
  },

  saveValue(id, key, value) {
    return this.ajaxCall({action: "saveval", id, key, value})
  },

  getPrefetch() {
    return this.ajaxCall({action: "prefetch"})
  },

})
