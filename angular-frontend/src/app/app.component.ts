import { Component } from '@angular/core';
import { Router, RouterOutlet } from '@angular/router';
import { HeaderComponent } from './shared/header/header.component';  // importa el header

@Component({
  selector: 'app-root',
  standalone: true,    
  imports: [RouterOutlet, HeaderComponent],  // añadir header aquí también
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'angular-frontend';
  currentRoute: string = '';

  constructor(private router: Router) {
    this.router.events.subscribe(() => {
      this.currentRoute = this.router.url;
    });
  }

  shouldShowLogoutButton(): boolean {
    return !(this.currentRoute.includes('login') || this.currentRoute.includes('reporte'));
  }
}
