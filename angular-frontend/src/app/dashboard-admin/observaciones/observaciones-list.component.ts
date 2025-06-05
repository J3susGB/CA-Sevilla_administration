// src/app/dashboard-clasificacion/observaciones/observaciones-list.component.ts
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';

import { ObservacionService, Observacion } from '../../services/observacion.service';
import { CategoriaService, Categoria } from '../../services/categoria.service';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../shared/components/confirm-dialog/confirm-dialog.component';
import { ToastService, Toast } from '../../shared/services/toast.service';
import { AuthService } from '../../services/auth.service';

import { ObservacionModalComponent } from './observaciones-modal/observacion-modal.component'; 

@Component({
  selector: 'app-observaciones-list',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatDialogModule,
    MatIconModule,
    ConfirmDialogComponent
  ],
  templateUrl: './observaciones-list.component.html',
  styleUrls: ['./observaciones-list.component.css']
})
export class ObservacionesListComponent implements OnInit {
  observaciones: Observacion[] = [];
  grouped: Record<string, Observacion[]> = {};
  filterForm!: FormGroup;
  toasts: Toast[] = [];
  backLink = '/clasificacion';

  constructor(
    private obsSvc: ObservacionService,
    private catSvc: CategoriaService,
    private auth: AuthService,
    private dialog: MatDialog,
    private fb: FormBuilder,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    this.toastService.toasts$.subscribe(toasts => this.toasts = toasts);

    this.filterForm = this.fb.group({
      textFilter: [''],
      catFilter: ['']
    });

    this.filterForm.valueChanges.subscribe(() => this.applyFilter());
    this.load();
  }

  dismissToast(id: number) {
    this.toastService.removeToast(id);
  }

  load(): void {
    this.obsSvc.getAll().subscribe(resp => {
      this.observaciones = resp.data.sort((a, b) => a.codigo.localeCompare(b.codigo));
      this.applyFilter();
    });
  }

  applyFilter(): void {
    const { textFilter, catFilter } = this.filterForm.value;
    const tf = textFilter.trim().toLowerCase();
    const cf = catFilter.trim().toLowerCase();

    const filtered = this.observaciones.filter(o =>
      (!tf || o.descripcion.toLowerCase().includes(tf) || o.codigo.toLowerCase().includes(tf)) &&
      (!cf || o.categoria.toLowerCase().includes(cf))
    );

    this.grouped = filtered.reduce((acc: Record<string, Observacion[]>, o) => {
      acc[o.categoria] = acc[o.categoria] || [];
      acc[o.categoria].push(o);
      return acc;
    }, {});
  }

  add(): void {
    this.dialog
      .open(ObservacionModalComponent, {
        width: '500px',
        panelClass: 'user-modal-dialog'
      })
      .afterClosed().subscribe(created => {
        if (created) {
          this.load();
          this.toastService.show('Observación añadida ✅', 'success');
        }
      });
  }

  edit(o: Observacion): void {
    this.dialog
      .open(ObservacionModalComponent, {
        width: '500px',
        data: { observacion: o },
        panelClass: 'user-modal-dialog'
      })
      .afterClosed().subscribe(updated => {
        if (updated) {
          this.load();
          this.toastService.show('Actualizada ✅', 'success');
        }
      });
  }

  delete(o: Observacion): void {
    const data: ConfirmDialogData = {
      title: `¿Eliminar ${o.codigo}?`,
      message: 'Esta acción no se puede deshacer',
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
      .subscribe(conf => {
        if (!conf) return;

        this.obsSvc.delete(o.id).subscribe({
          next: () => {
            this.load();
            this.toastService.show('Observación eliminada 🗑️', 'error');
          },
          error: () => {
            this.toastService.show('Error al eliminar ❌', 'error');
          }
        });
      });
  }
}
