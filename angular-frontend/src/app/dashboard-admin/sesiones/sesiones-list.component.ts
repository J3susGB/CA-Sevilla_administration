import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';

import {
  ConfirmDialogComponent,
  ConfirmDialogData
} from '../../shared/components/confirm-dialog/confirm-dialog.component';
import { ToastService } from '../../shared/services/toast.service';

import { AsistenciaService, Sesion, Asistencia } from '../../services/asistencia.service';
import { AuthService } from '../../services/auth.service';
import { AsistenciaModalComponent } from './sesiones-modal/asistencia-modal.component';

@Component({
  selector: 'app-sesiones-list',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatDialogModule,
    ConfirmDialogComponent
  ],
  templateUrl: './sesiones-list.component.html',
  styleUrls: ['./sesiones-list.component.css']
})
export class SesionesListComponent implements OnInit {
  sesiones: Sesion[] = [];
  filteredSessions: Sesion[] = [];
  totalsByCategory: any[] = [];
  filteredTotals: any[] = [];

  filterForm!: FormGroup;
  viewMode: 'bySession' | 'byCategory' = 'bySession';
  backLink = '/';

  expandedSessions: Record<number, boolean> = {};
  expandedCategories: Record<string, boolean> = {};
  private categoryOrder = ['Provincial', 'Oficial', 'Auxiliar'];

  constructor(
    private fb: FormBuilder,
    private asiSvc: AsistenciaService,
    private auth: AuthService,
    private dialog: MatDialog,
    private toastService: ToastService
  ) { }

  ngOnInit() {
    if (!this.auth.getRoles().some(r =>
      ['ROLE_ADMIN', 'ROLE_CAPACITACION'].includes(r)
    )) return;

    this.backLink = this.auth.getRoles().includes('ROLE_ADMIN') ? '/admin' : '/capacitacion';

    this.filterForm = this.fb.group({
      nameFilter: [''],
      catFilter: ['']
    });
    this.filterForm.valueChanges.subscribe(() => this.applyFilter());

    this.loadSessions();
    this.loadTotals();
  }

  private parseDate(fecha: string): Date {
    const [d, m, y] = fecha.split('-').map(n => parseInt(n, 10));
    return new Date(y, m - 1, d);
  }

  private loadSessions() {
    this.asiSvc.listAll().subscribe(res => {
      this.sesiones = res.data.map(s => ({
        ...s,
        asistencias: this.sortArbitros(s.asistencias)
      }));
      this.sesiones.sort((a, b) => {
        const ca = this.categoryOrder.indexOf(a.categoria);
        const cb = this.categoryOrder.indexOf(b.categoria);
        if (ca !== cb) return ca - cb;
        return this.parseDate(a.fecha).getTime() - this.parseDate(b.fecha).getTime();
      });
      this.filteredSessions = [...this.sesiones];
      this.sesiones.forEach(s => this.expandedSessions[s.id] = false);
    });
  }

  private loadTotals() {
    this.asiSvc.totals().subscribe(res => {
      this.totalsByCategory = res.data.map(c => ({
        ...c,
        arbitros: this.sortArbitros(c.arbitros)
      }));
      this.totalsByCategory.sort((a, b) =>
        this.categoryOrder.indexOf(a.categoria) - this.categoryOrder.indexOf(b.categoria)
      );
      this.filteredTotals = [...this.totalsByCategory];
      this.totalsByCategory.forEach(c => this.expandedCategories[c.categoria] = false);
    });
  }

  private sortArbitros(arr: any[]): any[] {
    return [...arr].sort((a, b) => {
      const A = `${a.first_surname} ${a.second_surname || ''} ${a.name}`.toLowerCase();
      const B = `${b.first_surname} ${b.second_surname || ''} ${b.name}`.toLowerCase();
      return A.localeCompare(B);
    });
  }

  applyFilter() {
    const nf = (this.filterForm.value.nameFilter || '').trim().toLowerCase();
    const cf = (this.filterForm.value.catFilter || '').trim().toLowerCase();

    this.filteredSessions = this.sesiones
      .filter(s => !cf || s.categoria.toLowerCase().includes(cf))
      .map(s => ({
        ...s,
        asistencias: this.sortArbitros(
          s.asistencias.filter(a => {
            const full = `${a.first_surname} ${a.second_surname || ''} ${a.name}`.toLowerCase();
            return (!nf || a.nif.toLowerCase().includes(nf) || full.includes(nf));
          })
        )
      }))
      .sort((a, b) => {
        const ca = this.categoryOrder.indexOf(a.categoria);
        const cb = this.categoryOrder.indexOf(b.categoria);
        if (ca !== cb) return ca - cb;
        return this.parseDate(a.fecha).getTime() - this.parseDate(b.fecha).getTime();
      });

    this.filteredTotals = this.totalsByCategory
      .filter(c => !cf || c.categoria.toLowerCase().includes(cf))
      .map(c => ({
        ...c,
        arbitros: this.sortArbitros(
          c.arbitros.filter((a: any) => {
            const full = `${a.first_surname} ${a.second_surname || ''} ${a.name}`.toLowerCase();
            return (!nf || a.nif.toLowerCase().includes(nf) || full.includes(nf));
          })
        )
      }));
  }

  toggleView(mode: 'bySession' | 'byCategory') {
    this.viewMode = mode;
    this.applyFilter();
  }

  toggleSession(id: number) {
    this.expandedSessions[id] = !this.expandedSessions[id];
  }

  toggleCategory(cat: string) {
    this.expandedCategories[cat] = !this.expandedCategories[cat];
  }

  addAsistencia(session: Sesion) {
    this.dialog.open(AsistenciaModalComponent, {
      width: '600px',
      data: { session },
      panelClass: 'user-modal-dialog'
    })
    .afterClosed().subscribe(updated => {
      if (updated) {
        this.loadSessions();
        this.loadTotals();
        this.toastService.show('Asistencia añadida ✅', 'success');
      }
    });
  }

  editAsistencia(session: Sesion, asi: Asistencia) {
    this.dialog.open(AsistenciaModalComponent, {
      width: '600px',
      data: { session, asistencia: asi },
      panelClass: 'user-modal-dialog'
    })
    .afterClosed().subscribe(updated => {
      if (updated) {
        this.loadSessions();
        this.loadTotals();
        this.toastService.show('Asistencia actualizada ✅', 'success');
      }
    });
  }

  deleteAsistencia(arbitroId: number) {
    const data: ConfirmDialogData = {
      title: '¿Eliminar asistencia?',
      message: 'Esta acción no se puede deshacer.',
      confirmText: 'Eliminar',
      cancelText: 'Cancelar'
    };
    this.dialog.open(ConfirmDialogComponent, {
      data,
      panelClass: 'confirm-dialog-panel',
      backdropClass: 'confirm-dialog-backdrop'
    })
    .afterClosed().subscribe(confirmed => {
      if (!confirmed) return;
      this.asiSvc.delete(arbitroId).subscribe({
        next: () => {
          this.loadSessions();
          this.loadTotals();
          this.toastService.show('Asistencia eliminada 🗑️', 'error');
        },
        error: () => {
          this.toastService.show('Error al eliminar asistencia ❌', 'error');
        }
      });
    });
  }

  openBulkOnly() {
    this.dialog.open(AsistenciaModalComponent, {
      width: '600px',
      data: { session: null },
      panelClass: 'user-modal-dialog'
    })
    .afterClosed().subscribe(updated => {
      if (updated) {
        this.loadSessions();
        this.loadTotals();
        this.toastService.show('Carga masiva completada ✅', 'success');
      }
    });
  }
}
