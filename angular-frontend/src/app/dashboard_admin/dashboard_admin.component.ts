import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-dashboard-admin',
  standalone: true,
  imports: [RouterModule, CommonModule],
  templateUrl: './dashboard_admin.component.html',
  styleUrls: ['./dashboard_admin.component.css']
})
export class DashboardAdminComponent {
    vocaliasOpen = false;  // Control del acordeón
  
    toggleVocalias() {
      this.vocaliasOpen = !this.vocaliasOpen;
    }
}
