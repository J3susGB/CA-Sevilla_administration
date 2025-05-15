import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-dashboard-clasificacion',
  standalone: true,
  imports: [CommonModule],
  template: `
    <h1>Dashboard Clasificación</h1>
    <p>Bienvenido al área de Clasificación.</p>
  `
})
export class DashboardClasificacionComponent {}
