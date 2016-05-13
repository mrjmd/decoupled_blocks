(function (drupalSettings) {
  'use strict';

  // Source: http://gomakethings.com/vanilla-javascript-version-of-jquery-extend
  // Pass in the objects to merge as arguments.
  // For a deep extend, set the first argument to `true`.
  function extend() {
    // Variables.
    var extended = {};
    var deep = false;
    var i = 0;
    var length = arguments.length;

    // Check if a deep merge.
    if (Object.prototype.toString.call(arguments[0]) === '[object Boolean]') {
      deep = arguments[0];
      i++;
    }

    // Merge the object into the extended object.
    var merge = function (obj) {
      for (var prop in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, prop)) {
          // If deep merge and property is an object, merge properties.
          if (deep && Object.prototype.toString.call(obj[prop]) === '[object Object]') {
            extended[prop] = extend(true, extended[prop], obj[prop]);
          }
          else {
            extended[prop] = obj[prop];
          }
        }
      }
    };

    // Loop through each object and conduct a merge.
    for (; i < length; i++) {
      var obj = arguments[i];
      merge(obj);
    }

    return extended;
  }

  var config = {
    // Use typescript for compilation.
    transpiler: 'typescript',
    // Typescript compiler options.
    typescriptOptions: {
      emitDecoratorMetadata: true
    },
    // Map tells the System loader where to look for things.
    map: {
      app: drupalSettings.path.baseUrl + 'modules/pdb/modules/pdb_ng2/assets/app'
    },
    // Packages defines our app package.
    packages: {
      app: {
        main: 'app',
        defaultExtension: 'ts'
      },
      angular: {
        defaultExtension: false
      }
    }
  };

  if ('SystemJS' in drupalSettings) {
    config = extend(true, config, drupalSettings.SystemJS);
  }

  System.config(config);
  System.import('app').catch(console.error.bind(console));

})(drupalSettings);
