import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

import { AuthService } from '../services/auth.service';
import { ToastService, Toast } from '../shared/services/toast.service';

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

  roles: string[] = [];
  vocaliasOpen = false;

  constructor(
    private auth: AuthService,
    private http: HttpClient,
    private toastService: ToastService
  ) {
    this.roles = this.auth.getRoles();
  }

  toggleVocalias(): void {
    this.vocaliasOpen = !this.vocaliasOpen;
  }

  descargarClasificacion(categoria: string): void {
    const url = `http://localhost:8000/api/clasificacion/${categoria}`;

    // Mostramos un toast "info" temporal
    this.toastService.show('Generando archivo...', 'info');

    this.http.get(url, { responseType: 'blob' }).subscribe({
      next: (blob) => {
        // Descarga del archivo
        const a = document.createElement('a');
        const objectUrl = URL.createObjectURL(blob);
        a.href = objectUrl;
        a.download = `Clasificacion_${categoria}.xlsx`;
        a.click();
        URL.revokeObjectURL(objectUrl);

        // Muestra el toast final tras un pequeño delay
        setTimeout(() => {
          this.toastService.show(`Clasificación de ${categoria} descargada ✅`, 'success');
        }, 3500);
      },
      error: (err) => {
        console.error('Error al descargar:', err);

        // Muestra error tras un pequeño delay
        setTimeout(() => {
          this.toastService.show(`Error al descargar la clasificación de ${categoria} ❌`, 'error');
        }, 500);
      }
    });
  }

}
