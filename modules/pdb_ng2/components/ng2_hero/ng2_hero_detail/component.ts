import { Component, Input } from '@angular/core';

import { Hero } from '../hero';

@Component({
  moduleId: __moduleName,
  selector: 'ng2-hero-detail',
  templateUrl: 'template.html',
})
export class Ng2HeroDetail {
  @Input() 
  hero: Hero;
}
