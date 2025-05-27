import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';    // <-- importa esto
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-dashboard-capacitacion',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule
  ],
  templateUrl: './dashboard-capacitacion.component.html'
  
})
export class DashboardCapacitacionComponent {

  // declara la propiedad roles
    roles: string[] = [];
  
    constructor(private auth: AuthService) {
      // inicialízala en el constructor
      this.roles = this.auth.getRoles();
    }
}
