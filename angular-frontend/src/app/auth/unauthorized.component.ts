import { Component } from '@angular/core';

@Component({
  selector: 'app-unauthorized',
  standalone: true,
  template: `
    <div style="text-align:center; margin-top:2rem;">
      <h2>🚫 Acceso denegado</h2>
      <p>No tienes permiso para ver esta página.</p>
      <a routerLink="/">Volver al login</a>
    </div>
  `
})
export class UnauthorizedComponent {}
