import { Component } from '@angular/core';
import { Router }     from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService }  from '../../services/auth.service';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css'],
})
export class HeaderComponent {
  constructor(
    private router: Router,
    public authService: AuthService  // público para usar getUsername() en el template
  ) {}

  shouldShowLogoutButton(): boolean {
    const url = this.router.url;
    return !(url === '/' || url.includes('login') || url.includes('reporte'));
  }

  logout(): void {
    // 1) Limpiar estado local (token + username)
    this.authService.logout();

    // 2) Redirigir al login
    this.router.navigate(['/login']);
  }
}
