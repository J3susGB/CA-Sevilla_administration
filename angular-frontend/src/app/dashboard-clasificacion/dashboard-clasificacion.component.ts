import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';    
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-dashboard-clasificacion',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule
  ],
  templateUrl: './dashboard-clasificacion.component.html'
  
})
export class DashboardClasificacionComponent {

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
}
