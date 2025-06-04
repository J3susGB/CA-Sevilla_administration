import { Component } from '@angular/core';
import { Router, RouterOutlet } from '@angular/router';
import { HeaderComponent } from './shared/header/header.component';  // importa el header
import { ToastComponent } from './shared/components/toast/toast.component';

import {
  trigger,
  transition,
  style,
  animate,
  query,
  group
} from '@angular/animations';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, HeaderComponent, ToastComponent],  // Inyectar header y toast
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
  animations: [
    trigger('routeAnimations', [
      transition('* <=> *', [
        query(':enter, :leave', [
          style({ display: 'block', width: '100%' }) 
        ], { optional: true }),

        query(':leave', [
          style({ opacity: 1 }),
          animate('150ms ease-out', style({ opacity: 0 }))
        ], { optional: true }),

        query(':enter', [
          style({ opacity: 0 }),
          animate('150ms ease-in', style({ opacity: 1 }))
        ], { optional: true }),
      ])
    ])
  ]
})
export class AppComponent {
  title = 'angular-frontend';

  prepareRoute(outlet: RouterOutlet) {
    return outlet?.activatedRouteData?.['animation'] || '';
  }
}
