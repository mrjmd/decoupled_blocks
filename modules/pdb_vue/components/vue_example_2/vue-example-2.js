// Create a root instance for each block
var vueElements = document.getElementsByClassName('vue-example-2');
var count = vueElements.length;

// Loop through each block
for (var i = 0; i < count; i++) {
  // Create a vue instance
  new Vue({
    el: vueElements[0],
    data: {
      message: 'Hello Vue!'
    }
  });
}
