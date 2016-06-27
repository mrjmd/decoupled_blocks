import {Component, ApplicationRef, ReflectiveInjector, createPlatform} from "@angular/core";
import {BROWSER_PROVIDERS, BROWSER_APP_PROVIDERS} from "@angular/platform-browser";
import {BROWSER_APP_COMPILER_PROVIDERS} from "@angular/platform-browser-dynamic";
import "rxjs/add/operator/map";

import {ScrollLoader} from '../classes/scroll-loader';
import {GlobalProviders} from '../classes/global-providers';

var injectables = drupalSettings.pdb.ng2.global_injectables;
var globalProviders = new GlobalProviders(injectables);
var importPromises = globalProviders.importGlobalInjectables();

// Dynamically load all globally shared @Injectable services and pass as
// providers into main app bootstrap.
Promise.all(importPromises).then((globalServices) => {
  // array of providers to pass into longform bootstrap to make @Injectable
  // services shared globally.
  var globalProvidersArray = globalProviders.createGlobalProvidersArray(globalServices);

  // components contains metadata about all ng2 components on the page.
  let components = drupalSettings.pdb.ng2.components;

  let platform = createPlatform(ReflectiveInjector.resolveAndCreate(BROWSER_PROVIDERS));
  let app = ReflectiveInjector
    .resolveAndCreate([
      BROWSER_APP_PROVIDERS,
      BROWSER_APP_COMPILER_PROVIDERS,
      globalProvidersArray
    ], platform.injector)
    .get(ApplicationRef);

  let loader = new ScrollLoader(app, components);
  loader.initialize();
});
