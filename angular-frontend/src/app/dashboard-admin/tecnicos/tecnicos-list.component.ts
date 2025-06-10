// src/app/dashboard-admin/tecnicos/tecnicos-list.component.ts

import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { MatFormFieldModule }  from '@angular/material/form-field';
import { MatInputModule }      from '@angular/material/input';
import { MatButtonModule }     from '@angular/material/button';
import { MatIconModule }       from '@angular/material/icon';
import { MatDialogModule, MatDialog }  from '@angular/material/dialog';

import {
  ConfirmDialogComponent,
  ConfirmDialogData
} from '../../shared/components/confirm-dialog/confirm-dialog.component';
import { ToastService } from '../../shared/services/toast.service';

import { TecnicoService, TecnicoSession, TecnicoNote, TecnicoReportByCategory } from '../../services/tecnico.service';
import { AuthService } from '../../services/auth.service';
import { TecnicosModalComponent } from './tecnicos-modal/tecnicos-modal.component';

@Component({
  selector: 'app-tecnicos-list',
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
  templateUrl: './tecnicos-list.component.html',
  styleUrls: ['./tecnicos-list.component.css']
})
export class TecnicosListComponent implements OnInit {
  /** Todas las sesiones técnicas */
  sessions: TecnicoSession[] = [];
  /** Copia filtrada (por nombre y/o categoría) */
  filteredSessions: TecnicoSession[] = [];

  /** Datos agrupados para vista “por categoría” */
  reportByCategory: TecnicoReportByCategory[] = [];
  filteredReport: TecnicoReportByCategory[] = [];

  filterForm!: FormGroup;
  /** Modo de vista: 'bySession' o 'byCategory' */
  viewMode: 'bySession' | 'byCategory' = 'bySession';

  /** Link “Atrás” según rol */
  backLink = '/';

  /** Control collapsible para sesiones (índice: session.id → boolean) */
  expandedSessions: Record<number, boolean> = {};

  /** Control collapsible para categorías (índice: categoría → boolean) */
  expandedCategories: Record<string, boolean> = {};

  /** Orden predefinido de categorías */
  private categoryOrder = ['Provincial', 'Oficial', 'Auxiliar'];

  constructor(
    private fb: FormBuilder,
    private tecSvc: TecnicoService,
    private auth: AuthService,
    private dialog: MatDialog,
    private toast: ToastService
  ) { }

  ngOnInit() {
    // Solo roles con ROLE_ADMIN o ROLE_CAPACITACION pueden acceder
    if (!this.auth.getRoles().some(r =>
      ['ROLE_ADMIN','ROLE_CAPACITACION'].includes(r)
    )) return;

    // Definimos el enlace “Atrás” en función del rol
    this.backLink = this.auth.getRoles().includes('ROLE_ADMIN')
      ? '/admin'
      : '/capacitacion';

    // Construimos el formGroup para los filtros (nombre + categoría)
    this.filterForm = this.fb.group({
      nameFilter: [''],
      catFilter:  ['']
    });
    // Cada vez que cambie el filtro, aplicamos
    this.filterForm.valueChanges.subscribe(() => this.applyFilter());

    // Cargar datos:
    this.loadSessions();    // carga GET /api/tecnicos
    this.loadReport();      // carga GET /api/tecnicos/report
  }

  /** Convierte “dd-MM-YYYY” a Date para comparar fechas */
  private parseDate(fecha: string): Date {
    const [d, m, y] = fecha.split('-').map(n => parseInt(n, 10));
    return new Date(y, m - 1, d);
  }

  // ───────────────────────────────────────────────────────────────────
  /** Carga TODAS las sesiones con sus notas (GET /api/tecnicos) */
  private loadSessions() {
    this.tecSvc.listAll().subscribe(res => {
      // Ordenamos internamente las notas de cada sesión por apellido+nombre
      this.sessions = res.data.map(s => ({
        ...s,
        notas: this.sortArbitros(s.notas)
      }));
      // Ordenamos sesiones: primero por categoría (según categoryOrder), luego por fecha ascendente
      this.sessions.sort((a, b) => {
        const ca = this.categoryOrder.indexOf(a.categoria);
        const cb = this.categoryOrder.indexOf(b.categoria);
        if (ca !== cb) return ca - cb;
        return this.parseDate(a.fecha).getTime() - this.parseDate(b.fecha).getTime();
      });
      // Copiamos a filteredSessions
      this.filteredSessions = [...this.sessions];
      // Inicializamos el estado “colapsado” de cada sesión
      this.sessions.forEach(s => this.expandedSessions[s.id] = false);
    });
  }

  // ───────────────────────────────────────────────────────────────────
  /** Carga el reporte “por categoría” (GET /api/tecnicos/report) */
  private loadReport() {
    this.tecSvc.report().subscribe(res => {
      // Ordenamos categorías según categoryOrder
      this.reportByCategory = res.data.slice().sort((a, b) =>
        this.categoryOrder.indexOf(a.categoria)
        - this.categoryOrder.indexOf(b.categoria)
      );
      // Duplicamos el array para filtrado
      this.filteredReport = [...this.reportByCategory];
      // Inicializamos el estado “colapsado” de cada categoría
      this.reportByCategory.forEach(c => this.expandedCategories[c.categoria] = false);
    });
  }

  /** Ordena un array de notas por apellido+nombre (alfabéticamente) */
  private sortArbitros(arr: TecnicoNote[]): TecnicoNote[] {
    return (arr || []).slice().sort((x, y) => {
      const A = `${x.first_surname} ${x.second_surname || ''} ${x.name}`.toLowerCase();
      const B = `${y.first_surname} ${y.second_surname || ''} ${y.name}`.toLowerCase();
      return A.localeCompare(B);
    });
  }

  /** Aplica los filtros de “nombre” y “categoría” */
  applyFilter() {
    const nf = (this.filterForm.value.nameFilter || '').trim().toLowerCase();
    const cf = (this.filterForm.value.catFilter  || '').trim().toLowerCase();

    // ── FILTRADO “Por Sesión” ──────────────────────────────────────────
    this.filteredSessions = this.sessions
      .filter(s => {
        // 1) Filtrar por categoría si catFilter no está vacío
        if (cf && !s.categoria.toLowerCase().includes(cf)) {
          return false;
        }
        return true;
      })
      .map(s => {
        // 2) Dentro de cada sesión, filtrar la lista de notas por nombre/NIF si nameFilter no está vacío
        const notasFiltradas = this.sortArbitros(
          s.notas.filter((n: TecnicoNote) => {
            if (!nf) return true;
            const fullName = `${n.first_surname} ${n.second_surname || ''} ${n.name}`.toLowerCase();
            return (
              n.nif.toLowerCase().includes(nf) ||
              fullName.includes(nf)
            );
          })
        );
        return {
          ...s,
          notas: notasFiltradas
        };
      })
      .filter(s => {
        // 3) Si nameFilter estaba activo, y tras filtrar notas no queda ninguna,
        //    descartamos toda la sesión (para no mostrar una tabla vacía).
        if (nf && s.notas.length === 0) {
          return false;
        }
        return true;
      })
      .sort((a, b) => {
        // 4) Volver a ordenar: categoría (según categoryOrder) y fecha
        const ca = this.categoryOrder.indexOf(a.categoria);
        const cb = this.categoryOrder.indexOf(b.categoria);
        if (ca !== cb) return ca - cb;
        return this.parseDate(a.fecha).getTime() - this.parseDate(b.fecha).getTime();
      });

    // ── FILTRADO “Por Categoría” ────────────────────────────────────────
    /**
     * Lógica:
     *   1) Filtrar categorías por el filtro de texto en la categoría (catFilter).
     *   2) Para cada categoría, iterar sobre cada examen (c.exams[examNumber]) y filtrar el array de notas
     *      por nombre/NIF (nameFilter).
     *   3) Si tras filtrar los notas de un examen quedan 0, eliminar completamente ese examen.
     *   4) Si tras procesar todos los exámenes de la categoría quedan 0 exámenes, eliminar la categoría.
     */
    this.filteredReport = this.reportByCategory
      .map(catObj => {
        // 1) Si catFilter no está vacío y no coincide la categoría, descartamos esta categoría:
        if (cf && !catObj.categoria.toLowerCase().includes(cf)) {
          return null;
        }

        // 2) Deconstruimos el objeto de la categoría para crear un clon filtrado:
        const examsMap = catObj.exams;
        const newExamsMap: { [key: number]: typeof examsMap[number] } = {};

        // 3) Recorremos cada número de examen (clave) en el map
        Object.keys(examsMap).forEach(key => {
          const examNumber = parseInt(key, 10);
          const notasArray = examsMap[examNumber] || [];

          // Filtrar este array de notas según nameFilter:
          const notasFiltradas = notasArray.filter(n => {
            if (!nf) return true;
            const fullName = `${n.first_surname} ${n.second_surname || ''} ${n.name}`.toLowerCase();
            return (
              n.first_surname.toLowerCase().includes(nf) ||
              (n.second_surname && n.second_surname.toLowerCase().includes(nf)) ||
              n.name.toLowerCase().includes(nf) ||
              fullName.includes(nf)
            );
          });

          // Si tras filtrar quedan notas, las guardamos en newExamsMap
          if (notasFiltradas.length > 0) {
            newExamsMap[examNumber] = this.sortArbitrosReport(notasFiltradas);
          }
          // Si no queda ninguna nota tras el filtro, no incluimos este examen en newExamsMap
        });

        // 4) Si tras procesar todos los exámenes no hay ninguno en newExamsMap,
        //    devolvemos `null` para indicar que se elimina esta categoría
        if (Object.keys(newExamsMap).length === 0) {
          return null;
        }

        // 5) Si sí quedan exámenes, devolvemos la categoría con su nuevo objeto exams:
        return {
          categoria_id: catObj.categoria_id,
          categoria:    catObj.categoria,
          exams:        newExamsMap
        } as TecnicoReportByCategory;
      })
      // 6) Filtramos los `null` (categorías que no coincidieron o quedaron sin exámenes):
      .filter(catObj => catObj !== null) as TecnicoReportByCategory[];
  }

  /** Cambia la vista: 'bySession' o 'byCategory' */
  toggleView(mode: 'bySession' | 'byCategory') {
    this.viewMode = mode;
    this.applyFilter();
  }

  /** Expande/colapsa la sección de una sesión concreta */
  toggleSession(id: number) {
    this.expandedSessions[id] = !this.expandedSessions[id];
  }

  /** Expande/colapsa la sección de una categoría concreta */
  toggleCategory(cat: string) {
    this.expandedCategories[cat] = !this.expandedCategories[cat];
  }

  // ───────────────────────────────────────────────────────────────────
  /** Abre el modal para **añadir** una nota técnica en esta sesión */
  addNota(session: TecnicoSession) {
    this.dialog.open(TecnicosModalComponent, {
      width: '600px',
      panelClass: 'user-modal-dialog',
      data: { session }
    })
      .afterClosed().subscribe(ok => {
        if (ok) {
          this.loadSessions();
          this.loadReport();
          this.toast.show('Nota añadida con éxito', 'success');
        }
      });
  }

  /** Abre el modal para **editar** una nota existente */
  editNota(session: TecnicoSession, nota: TecnicoNote) {
    this.dialog.open(TecnicosModalComponent, {
      width: '600px',
      panelClass: 'user-modal-dialog',
      data: { session, nota }
    })
      .afterClosed().subscribe(ok => {
        if (ok) {
          this.loadSessions();
          this.loadReport();
          this.toast.show('Nota actualizada con éxito', 'success');
        }
      });
  }

  /** Abre el modal **solo** para carga masiva (sin sesión preseleccionada) */
  openBulkOnly() {
    this.dialog.open(TecnicosModalComponent, {
      width: '600px',
      panelClass: 'user-modal-dialog',
      backdropClass: 'confirm-dialog-backdrop',
      data: { session: null }
    })
      .afterClosed()
      .subscribe(ok => {
        if (ok) {
          this.loadSessions();
          this.loadReport();
          this.toast.show('Carga masiva completada con éxito', 'success');
        }
      });
  }

  /** Elimina una nota técnica (DELETE /api/tecnicos/{id}) */
  deleteNota(id?: number) {
    if (!id) return;
    const data: ConfirmDialogData = {
      title: '¿Eliminar nota técnica?',
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
        this.tecSvc.delete(id).subscribe(() => {
          this.loadSessions();
          this.loadReport();
          this.toast.show('Nota eliminada 🗑️', 'error');
        }, () => this.toast.show('Error al eliminar', 'error'));
      });
  }

  // ───────────────────────────────────────────────────────────────────
  /**
   * Devuelve un array de números de examen (1,2,3,…) ordenados,
   * a partir de las claves del objeto `c.exams`.
   */
  getSortedExamNumbers(examsMap: { [key: number]: any[] }): number[] {
    return Object.keys(examsMap)
      .map(key => parseInt(key, 10))
      .sort((a, b) => a - b);
  }

  /**
   * Ordena alfabéticamente el array de árbitros en cada examen
   * para la vista “por categoría”.
   */
  sortArbitrosReport(arr: Array<{ first_surname: string; second_surname?: string; name: string; nota: number; repesca: boolean }>) {
    return (arr || []).slice().sort((x, y) => {
      const A = `${x.first_surname} ${x.second_surname || ''} ${x.name}`.toLowerCase();
      const B = `${y.first_surname} ${y.second_surname || ''} ${y.name}`.toLowerCase();
      return A.localeCompare(B);
    });
  }
}
