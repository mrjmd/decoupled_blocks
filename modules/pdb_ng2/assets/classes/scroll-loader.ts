/**
 * This is a generic concrete class for loading components.
 * - Manually load in components by providing an array of component elements.
 * - Autoload components on page load.
 * - Load components on demand when a compnent element is scolled, resized or
 *   orientationchanged into view.
 */
import {Renderer,ElementRef, ApplicationRef, DynamicComponentLoader, Injector, provide, platform, enableProdMode, PACKAGE_ROOT_URL} from "angular2/core";
import {APP_COMPONENT_REF_PROMISE} from "angular2/src/core/application_tokens";
import {Observable} from "rxjs/Observable";
import "rxjs/add/observable/fromEvent";
import "rxjs/add/operator/debounceTime";

if (drupalSettings.pdb.configuration.development_mode === 0) {
  enableProdMode();
}

export class ScrollLoader {
  app: any[];
  components: any;
  componentIds: string[];

  constructor(app: ApplicationRef, components) {
    this.app = app;
    this.components = components;
    this.componentIds = Object.keys(components);
    this.subscribe();
  }

  /**
   * Initialize any components in view on page load.
   */
  initialize() {
    // Originally, this query was using a concrete class named "content-wrap"
    // but we may need a generic approach that don't require to add
    // a specific class to pages using ng2. Just for now, use "body" element.
    let content = document.querySelector("body");

    if (content) {
      this.checkElements(this.componentIds);
    }
  }

  /**
   * Subscribe to window scroll, resize and orientationchange events, binding
   * a checkElements call to check if components are now in view.
   */
  subscribe() {
    Observable.fromEvent(window, "scroll")
      .debounceTime(200)
      .subscribe(x => {
        this.checkElements(this.componentIds);
      }, this);

    Observable.fromEvent(window, "resize")
      .debounceTime(200)
      .subscribe(x => {
        this.checkElements(this.componentIds);
      }, this);

    Observable.fromEvent(window, "orientationchange")
      .subscribe(x => {
        this.checkElements(this.componentIds);
      }, this);
  }

  /**
   * Unsubscribe elements which have already been bootstrapped by deleting from
   * elements array.
   *
   * @param {string} id of element to be unsubscribed
   */
  unsubscribe(id) {
    if (this.componentIds.length) {
      let index = this.componentIds.indexOf(id);

      if (index > -1) {
        // immutable splice
        this.componentIds = [
          ...this.componentIds.slice(0, index),
          ...this.componentIds.slice(index + 1)
        ];
      }
    }
  }

  /**
   * Helper function to check if any of the elements are in view. If an element
   * is in view, its corresponding component is loaded and initialized
   *
   * @param {Array.<string>} ids of component elements
   */
  checkElements(ids) {
    ids.forEach((id, index) => {
      let el = document.getElementById(id);

      if (el && this.elementInViewport(el)) {
        // assuming if innerHTML is empty module has not been loaded
        if (el.innerHTML.length === 0) {
          let elementName = this.components[id]["element"];
          let ngClassName = this.convertToNgClassName(elementName);
          let selector = "#" + id;
          this.bootstrapComponent(id, ngClassName, selector);
        }
      }
    });
  }

  /**
   * Bootstraps individual component into the DOM using System.js.
   *
   * @param {string} id - component element id
   * @param {string} ngClassName - Component Class Name
   * @param {string} selector - selctor of DOM element to bootstrap into
   */
  bootstrapComponent(id, ngClassName, selector) {
    let componentFile = Drupal.url(drupalSettings.pdb.ng2.components[id]["uri"]);

    System.import(componentFile).then((componentClass) => {
      return componentClass[ngClassName];
    }).then((component) => {
      // use dynamic bootstrap function
      this.bootstrapWithCustomSelector(this.app, component, selector).then((bootstrappedComponent) => {
        // successfully bootstrapped, stop checking if in viewport
        this.unsubscribe(id);
      });
    });
  }

  /**
   * Dynamically bootstrap a component into a selector.
   * This is a hack.
   * Courtesy Tobias Bosch.
   * @see https://github.com/angular/angular/issues/7136
   *
   * @param {object} app - root application
   * @param {object} component - the instance of component to bootstrap
   * @param {string} selector - the DOM element to bootstrap into
   * @returns {object} - ComponentRef of boostrapped component
   */
  bootstrapWithCustomSelector(app, component, selector: string) {
    let bootstrapProviders = [
      provide(APP_COMPONENT_REF_PROMISE, {
        useFactory: (dynamicComponentLoader: DynamicComponentLoader, appRef: ApplicationRef, injector: Injector) => {
            let ref;
            return dynamicComponentLoader.loadAsRoot(component, selector, injector, () => {
              appRef._unloadComponent(ref);
            }).then((componentRef) => ref = componentRef)
          },
          deps: [DynamicComponentLoader, ApplicationRef, Injector]
      })
    ];

    return app.bootstrap(component, bootstrapProviders);
  }

  /**
   * Helper function to convert component name to Angular 2 ClassName.
   *
   * @param {string} - elementName in form "wu-favorites"
   * @returns {string} - ng2 class name in form "WuFavorites"
   */
  convertToNgClassName(elementName) {
    return (elementName.toLowerCase().charAt(0).toUpperCase() + elementName.slice(1)).replace(/-(.)/g, (match, group1) => {
      return group1.toUpperCase();
    });
  }

  /**
   * Checks to see if an element is in view.
   *
   * @param {object} el - element to check
   * @returns {boolean} - in viewport
   *
   * @see based on SO answer: http://stackoverflow.com/a/23234031/1774183
   * Fixes firefox issue and also loads if any part of element is in
   * view. Returns elements not notInView (in viewport).
   */
  elementInViewport(el) {
    let rect = el.getBoundingClientRect();

    return (
      !(rect.bottom < 0 || rect.right < 0 || rect.left > (window.innerWidth || document.documentElement.clientWidth) || rect.top > (window.innerHeight || document.documentElement.clientHeight))
    );
  }
}
