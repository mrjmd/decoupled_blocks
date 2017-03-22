# vue_example_webpack

> An example for using webpack with vue in pdb

## Build Setup

``` bash
# install dependencies
npm install

# serve with hot reload at localhost:8080
npm run dev

# build for production with minification
npm run build
```

For detailed explanation on how things work, consult the [docs for vue-loader](http://vuejs.github.io/vue-loader).


# Using Webpack with PDB
The production build with webpack needs some additional changes so that it plays
nice with Drupal and PDB

```json
// Set up the path to the dist folder from within Drupal
module.exports.output.publicPath = '/modules/contrib/pdb/modules/pdb_vue/components/vue_example_webpack/dist/'

// Since the vue.js library is already installed via Drupal, we can skip it
module.exports.externals = {
  "vue": "Vue"
}
```

The index.html file is not used by drupal, but it will be used for rapid
development using `npm run dev` from within the component directory which will
open the index.html file in a local browser window isolated from Drupal.
