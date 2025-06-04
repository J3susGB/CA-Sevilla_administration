// src/app/dashboard-admin/simulacros/simulacros-list.component.ts

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

import { SimulacrosService, Simulacro } from '../../services/simulacros.service';
import { CategoriaService } from '../../services/categoria.service';
import { AuthService } from '../../services/auth.service';
import { ToastService, Toast } from '../../shared/services/toast.service';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../shared/components/confirm-dialog/confirm-dialog.component';
import { SimulacrosModalComponent } from './simulacros-modal/simulacros-modal.component'; 

@Component({
  selector: 'app-simulacros-list',
  standalone: true,
  templateUrl: './simulacros-list.component.html',
  styleUrls: ['./simulacros-list.component.css'],
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
    SimulacrosModalComponent
  ]
})
export class SimulacrosListComponent implements OnInit {
  simulacros: Simulacro[] = [];
  filteredSimulacros: Simulacro[] = [];
  filterForm!: FormGroup;
  toasts: Toast[] = [];
  backLink = '/';

  constructor(
    private simSvc: SimulacrosService,
    private catSvc: CategoriaService,
    private auth: AuthService,
    private dialog: MatDialog,
    private fb: FormBuilder,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    const roles = this.auth.getRoles();
    if (roles.includes('ROLE_ADMIN')) this.backLink = '/admin';
    else if (roles.includes('ROLE_CLASIFICACION')) this.backLink = '/entrenamientos';

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
      simulacros: this.simSvc.getAll(),
      categorias: this.catSvc.getAll()
    }).subscribe(({ simulacros, categorias }) => {
      const mapCat = categorias.reduce<Record<number, string>>((acc, c) => {
        acc[c.id] = c.nombre;
        return acc;
      }, {});

      this.simulacros = simulacros.data.map(s => ({
        ...s,
        categoria: mapCat[s.categoria_id] || '—'
      }));

      this.filteredSimulacros = [...this.simulacros];
      this.applyFilter();
    });
  }

  applyFilter(): void {
    const { nameFilter, catFilter } = this.filterForm.value;
    const nf = nameFilter.trim().toLowerCase();
    const cf = catFilter.trim().toLowerCase();

    this.filteredSimulacros = this.simulacros.filter(s => {
      const fullName = `${s.first_surname} ${s.second_surname || ''} ${s.name}`.toLowerCase();
      const matchesName = !nf || fullName.includes(nf);
      const matchesCat = !cf || (s.categoria || '').toLowerCase().includes(cf);
      return matchesName && matchesCat;
    });
  }

  addSimulacro(): void {
    this.dialog
      .open(SimulacrosModalComponent, {
        width: '700px',
        panelClass: 'user-modal-dialog'
      })
      .afterClosed()
      .subscribe(created => {
        if (created) {
          this.load();
          this.toastService.show('Simulacro añadido ✅', 'success');
        }
      });
  }

  editSimulacro(s: Simulacro): void {
    this.dialog
      .open(SimulacrosModalComponent, {
        width: '700px',
        data: { simulacro: s },
        panelClass: 'user-modal-dialog'
      })
      .afterClosed()
      .subscribe(updated => {
        if (updated) {
          this.load();
          this.toastService.show('Simulacro actualizado ✅', 'success');
        }
      });
  }

  deleteSimulacro(s: Simulacro): void {
    const surnames = [s.first_surname, s.second_surname].filter(Boolean).join(' ');
    const title = `¿Eliminar a ${surnames}, ${s.name}?`;

    const data: ConfirmDialogData = {
      title,
      message: 'Esta acción eliminará el simulacro asignado.',
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

        this.simSvc.delete(s.id).subscribe({
          next: () => {
            this.load();
            this.toastService.show('Simulacro eliminado 🗑️', 'error');
          },
          error: () => {
            this.toastService.show('Error al eliminar ❌', 'error');
          }
        });
      });
  }
}
