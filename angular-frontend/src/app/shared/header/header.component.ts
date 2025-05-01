import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css'],
})
export class HeaderComponent {
  
  constructor(private router: Router) {}

  shouldShowLogoutButton(): boolean {
    const url = this.router.url;
    return !(url === '/' || url.includes('login') || url.includes('reporte'));
  }
}
