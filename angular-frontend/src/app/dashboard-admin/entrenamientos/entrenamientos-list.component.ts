// src/app/dashboard-admin/entrenamientos/entrenamientos-list.component.ts

import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';

import { forkJoin } from 'rxjs';

import { EntrenamientosService, Entrenamiento } from '../../services/entrenamientos.service';
import { CategoriaService } from '../../services/categoria.service';
import { AuthService } from '../../services/auth.service';
import { ToastService, Toast } from '../../shared/services/toast.service';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../shared/components/confirm-dialog/confirm-dialog.component';
import { EntrenamientosModalComponent } from './entrenamientos-modal/entrenamientos-modal.component';
import { TotalEntrenamientoPipe } from '../../pipes/totalEntrenamiento.pipe'; 

@Component({
  selector: 'app-entrenamientos-list',
  standalone: true,
  templateUrl: './entrenamientos-list.component.html',
  styleUrls: ['./entrenamientos-list.component.css'],
  imports: [
    CommonModule,
    RouterModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    ConfirmDialogComponent,
    TotalEntrenamientoPipe
  ]
})
export class EntrenamientosListComponent implements OnInit {
  entrenamientos: Entrenamiento[] = [];
  filtered: Entrenamiento[] = [];
  filterForm!: FormGroup;
  toasts: Toast[] = [];
  backLink = '/';

  constructor(
    private entSvc: EntrenamientosService,
    private catSvc: CategoriaService,
    private auth: AuthService,
    private dialog: MatDialog,
    private fb: FormBuilder,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    const roles = this.auth.getRoles();
    if (roles.includes('ROLE_ADMIN')) this.backLink = '/admin';
    else if (roles.includes('ROLE_CLASIFICACION')) this.backLink = '/clasificacion';
    else if (roles.includes('ROLE_INFORMACION')) this.backLink = '/informacion';

    this.toastService.toasts$.subscribe(toasts => this.toasts = toasts);

    this.filterForm = this.fb.group({
      nameFilter: [''],
      catFilter: ['']
    });
    this.filterForm.valueChanges.subscribe(() => this.applyFilter());

    this.load();
  }

  dismissToast(id: number) {
    this.toastService.removeToast(id);
  }

  private load(): void {
    forkJoin({
      entrenamientos: this.entSvc.getAll(),
      categorias: this.catSvc.getAll()
    }).subscribe(({ entrenamientos, categorias }) => {
      const mapCat = categorias.reduce<Record<number, string>>((acc, c) => {
        acc[c.id] = c.nombre;
        return acc;
      }, {});

      this.entrenamientos = entrenamientos.data.map(e => ({
        ...e,
        categoria: mapCat[e.categoria_id] || '—'
      }));

      this.filtered = [...this.entrenamientos];
      this.applyFilter();
    });
  }

  applyFilter(): void {
    const { nameFilter, catFilter } = this.filterForm.value;
    const nf = nameFilter.trim().toLowerCase();
    const cf = catFilter.trim().toLowerCase();

    this.filtered = this.entrenamientos.filter(e => {
      const fullName = `${e.first_surname} ${e.second_surname || ''} ${e.name}`.toLowerCase();
      const matchesName = !nf || fullName.includes(nf);
      const matchesCat = !cf || (e.categoria || '').toLowerCase().includes(cf);
      return matchesName && matchesCat;
    });
  }

  addEntrenamiento(): void {
    this.dialog
      .open(EntrenamientosModalComponent, {
        width: '700px',
        panelClass: 'user-modal-dialog'
      })
      .afterClosed()
      .subscribe(created => {
        if (created) {
          this.load();
          this.toastService.show('Añadido ✅', 'success');
        }
      });
  }

  editEntrenamiento(e: any): void {
    this.dialog
      .open(EntrenamientosModalComponent, {
        width: '700px',
        data: { entrenamiento: e },
        panelClass: 'user-modal-dialog'
      })
      .afterClosed()
      .subscribe(updated => {
        if (updated) {
          this.load();
          this.toastService.show('Actualizado ✅', 'success');
        }
      });
  }

  deleteEntrenamiento(e: any): void {
    const surnames = [e.first_surname, e.second_surname].filter(Boolean).join(' ');
    const title = `¿Eliminar a ${surnames}, ${e.name}?`;

    const data: ConfirmDialogData = {
      title,
      message: 'Esta acción eliminará sus datos de asistencia mensual.',
      confirmText: 'Eliminar',
      cancelText: 'Cancelar'
    };

    this.dialog
      .open(ConfirmDialogComponent, {
        data,
        panelClass: 'confirm-dialog-panel',
        backdropClass: 'confirm-dialog-backdrop'
      })
      .afterClosed()
      .subscribe(confirmed => {
        if (!confirmed) return;

        this.entSvc.delete(e.id).subscribe({
          next: () => {
            this.load();
            this.toastService.show('Eliminado 🗑️', 'error');
          },
          error: () => {
            this.toastService.show('Error al eliminar ❌', 'error');
          }
        });
      });
  }
}
