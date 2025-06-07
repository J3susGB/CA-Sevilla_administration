import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { MatDialog } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { ConfirmDialogComponent, ConfirmDialogData } from '../shared/components/confirm-dialog/confirm-dialog.component';
import { ToastService } from '../shared/services/toast.service';
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

  constructor(
    private auth: AuthService,
    private dialog: MatDialog,
    private toastService: ToastService,
    private http: HttpClient

  ) {
    // inicialízala en el constructor
    this.roles = this.auth.getRoles();
  }

  toggleVocalias(): void {
    this.vocaliasOpen = !this.vocaliasOpen;
  }

  isAdmin(): boolean {
    return this.roles.includes('ROLE_ADMIN');
  }

  resetDatabase(): void {
    const data: ConfirmDialogData = {
      title: '¿Resetear base de datos?',
      message: 'Se eliminarán todos los datos de árbitros, informes, entrenamientos, etc. Esta acción no se puede deshacer.',
      confirmText: 'Sí, borrar todo',
      cancelText: 'Cancelar'
    };

    this.dialog.open(ConfirmDialogComponent, {
      data,
      panelClass: 'confirm-dialog-panel',
      backdropClass: 'confirm-dialog-backdrop'
    }).afterClosed().subscribe(confirmed => {
      if (!confirmed) return;

      this.http.post('http://localhost:8000/api/admin/reset', {}).subscribe({
        next: () => {
          this.toastService.show('Base de datos reseteada correctamente ✅', 'success');
        },
        error: () => {
          this.toastService.show('Error al resetear base de datos ❌', 'error');
        }
      });
    });
  }
}
