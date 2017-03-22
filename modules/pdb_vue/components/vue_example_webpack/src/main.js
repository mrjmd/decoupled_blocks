import Vue from 'vue'
import App from './App.vue'

// Create a root instance for each block
const vueElements = document.getElementsByClassName('vue-example-webpack');
const count = vueElements.length;

// Loop through each block
for (let i = 0; i < count; i++) {
  // Create a vue instance
  new Vue({
    el: vueElements[0],
    render: h => h(App)
  })
}
