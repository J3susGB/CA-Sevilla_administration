import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-dashboard-informacion',
  standalone: true,
  imports: [CommonModule],
  template: `
    <h1>Dashboard Información</h1>
    <p>Bienvenido al área de Información.</p>
  `
})
export class DashboardInformacionComponent {}
