import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService } from '../services/auth.service'; 


@Component({
  selector: 'app-dashboard-admin',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard-admin.component.html'
})
export class DashboardAdminComponent {
  // declara la propiedad roles
  roles: string[] = [];
  vocaliasOpen = false;

  constructor(private auth: AuthService) {
    // inicialízala en el constructor
    this.roles = this.auth.getRoles();
  }

  toggleVocalias(): void {
    this.vocaliasOpen = !this.vocaliasOpen;
  }

  isAdmin(): boolean {
    return this.roles.includes('ROLE_ADMIN');
  }
}
