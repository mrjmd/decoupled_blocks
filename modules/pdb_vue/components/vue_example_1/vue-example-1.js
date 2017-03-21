// Create a vue instance
// This will only work on a single block in a page. If you need to have multiple
// blocks render in a single page, then look at vue_example_2 or use components.
new Vue({
  el: '.vue-example-1',
  template: '<div class="test">{{ message }}</div>',
  data: {
    message: 'Hello Vue! This will only work on a single Example 1 block per page.'
  }
});
