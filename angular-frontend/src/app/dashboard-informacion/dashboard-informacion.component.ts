import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router'; 
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-dashboard-informacion',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule
  ],
  templateUrl: './dashboard-informacion.component.html'
  
})
export class DashboardInformacionComponent {

  // declara la propiedad roles
    roles: string[] = [];
  
    constructor(private auth: AuthService) {
      // inicialízala en el constructor
      this.roles = this.auth.getRoles();
    }

}
