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

import { TestService, TestSession, TestNote } from '../../services/test.service';
import { AuthService } from '../../services/auth.service';
import { TestModalComponent } from './tests-modal/tests-modal.component';

@Component({
  selector: 'app-tests-list',
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
  templateUrl: './tests-list.component.html',
  styleUrls: ['./tests-list.component.css']
})
export class TestsListComponent implements OnInit {
  sessions: TestSession[] = [];
  filteredSessions: TestSession[] = [];
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
    private testSvc: TestService,
    private auth: AuthService,
    private dialog: MatDialog,
    private toast: ToastService
  ) { }

  ngOnInit() {
    if (!this.auth.getRoles().some(r =>
      ['ROLE_ADMIN', 'ROLE_CAPACITACION'].includes(r)
    )) return;

    this.backLink = this.auth.getRoles().includes('ROLE_ADMIN')
      ? '/admin'
      : '/capacitacion';

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
    this.testSvc.listAll().subscribe(res => {
      this.sessions = res.data.map(s => ({
        ...s,
        notas: this.sortArbitros(s.notas)
      }));
      this.sessions.sort((a, b) => {
        const ca = this.categoryOrder.indexOf(a.categoria);
        const cb = this.categoryOrder.indexOf(b.categoria);
        if (ca !== cb) return ca - cb;
        return this.parseDate(a.fecha).getTime() - this.parseDate(b.fecha).getTime();
      });
      this.filteredSessions = [...this.sessions];
      this.sessions.forEach(s => this.expandedSessions[s.id] = false);
    });
  }

  private loadTotals() {
    this.testSvc.totals().subscribe(res => {
      this.totalsByCategory = res.data.map(c => ({
        ...c,
        arbitros: this.sortArbitros(c.arbitros)
      }));
      this.totalsByCategory.sort((a, b) =>
        this.categoryOrder.indexOf(a.categoria)
        - this.categoryOrder.indexOf(b.categoria)
      );
      this.filteredTotals = [...this.totalsByCategory];
      this.totalsByCategory.forEach(c => this.expandedCategories[c.categoria] = false);
    });
  }

  private sortArbitros(arr: any[]): any[] {
    return (arr || []).slice().sort((x, y) => {
      const A = `${x.first_surname} ${x.second_surname || ''} ${x.name}`.toLowerCase();
      const B = `${y.first_surname} ${y.second_surname || ''} ${y.name}`.toLowerCase();
      return A.localeCompare(B);
    });
  }

  applyFilter() {
    const nf = (this.filterForm.value.nameFilter || '').trim().toLowerCase();
    const cf = (this.filterForm.value.catFilter || '').trim().toLowerCase();

    this.filteredSessions = this.sessions
      .filter(s => !cf || s.categoria.toLowerCase().includes(cf))
      .map(s => ({
        ...s,
        notas: this.sortArbitros(
          s.notas.filter((n: any) => {
            const full = `${n.first_surname} ${n.second_surname || ''} ${n.name}`.toLowerCase();
            return !nf
              || n.nif.toLowerCase().includes(nf)
              || full.includes(nf);
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
            return !nf
              || a.nif.toLowerCase().includes(nf)
              || full.includes(nf);
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

  addTest(session: TestSession) {
    this.dialog.open(TestModalComponent, {
      width: '600px',
      panelClass: 'user-modal-dialog',
      data: { session }
    })
      .afterClosed().subscribe(ok => {
        if (ok) {
          this.loadSessions();
          this.loadTotals();
          this.toast.show('Nota añadida con éxito', 'success');
        }
      });
  }

  editTest(session: TestSession, nota: TestNote) {
    this.dialog.open(TestModalComponent, {
      width: '600px',
      panelClass: 'user-modal-dialog',
      data: { session, nota }
    })
      .afterClosed().subscribe(ok => {
        if (ok) {
          this.loadSessions();
          this.loadTotals();
          this.toast.show('Nota actualizada con éxito', 'success');
        }
      });
  }

  deleteTest(id?: number) {
    if (!id) return;
    const data: ConfirmDialogData = {
      title: '¿Eliminar nota?',
      message: 'Esta acción no se puede deshacer.',
      confirmText: 'Eliminar',
      cancelText: 'Cancelar'
    };
    this.dialog.open(ConfirmDialogComponent, {
      panelClass: 'confirm-dialog-panel',
      data
    })
      .afterClosed().subscribe(conf => {
        if (!conf) return;
        this.testSvc.delete(id).subscribe(() => {
          this.loadSessions();
          this.loadTotals();
          this.toast.show('Nota eliminada con éxito', 'error');
        }, () => this.toast.show('Error al eliminar', 'error'));
      });
  }

  openBulkOnly() {
    this.dialog.open(TestModalComponent, {
      width: '600px',
      panelClass: 'user-modal-dialog',    // tu clase de panel
      backdropClass: 'confirm-dialog-backdrop', // tu clase de backdrop
      data: { session: null }
    })
      .afterClosed()
      .subscribe(ok => {
        if (ok) {
          this.loadSessions();
          this.loadTotals();
          this.toast.show('Carga masiva completada con éxito', 'success');
        }
      });
  }
}
