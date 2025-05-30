import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';

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
        MatDialogModule
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

    constructor(
        private fb: FormBuilder,
        private asiSvc: AsistenciaService,
        private auth: AuthService,
        private dialog: MatDialog
    ) { }

    ngOnInit() {
        // Sólo ADMIN / CAPACITACIÓN
        if (!this.auth.getRoles().some(r =>
            ['ROLE_ADMIN', 'ROLE_CAPACITACION'].includes(r)
        )) {
            return;
        }

        // Enlace ATRÁS según rol
        const roles = this.auth.getRoles();
        this.backLink = roles.includes('ROLE_ADMIN')
            ? '/admin'
            : '/capacitacion';

        // Form de filtros
        this.filterForm = this.fb.group({
            nameFilter: [''],
            catFilter: ['']
        });
        this.filterForm.valueChanges.subscribe(() => this.applyFilter());

        // Carga inicial
        this.loadSessions();
        this.loadTotals();
    }

    private loadSessions() {
        this.asiSvc.listAll().subscribe(res => {
            // ordena las asistencias en cada sesión
            this.sesiones = res.data.map(s => ({
                ...s,
                asistencias: this.sortArbitros(s.asistencias)
            }));
            this.filteredSessions = [...this.sesiones];
        });
    }

    private loadTotals() {
        this.asiSvc.totals().subscribe(res => {
            // ordena los totales
            this.totalsByCategory = res.data.map(c => ({
                ...c,
                arbitros: this.sortArbitros(c.arbitros)
            }));
            this.filteredTotals = [...this.totalsByCategory];
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

        // Filtra sesiones
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
            }));

        // Filtra totales
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

    // ─────────────────────────────
    //  MODAL DE CREAR / EDITAR
    // ─────────────────────────────
    addAsistencia(session: Sesion) {
        const ref = this.dialog.open(AsistenciaModalComponent, {
            width: '600px',
            data: { session },
             panelClass: 'user-modal-dialog'
        });
        ref.afterClosed().subscribe(updated => {
            if (updated) {
                this.loadSessions();
                this.loadTotals();
            }
        });
    }

    editAsistencia(session: Sesion, asi: Asistencia) {
        const ref = this.dialog.open(AsistenciaModalComponent, {
            width: '600px',
            data: { session, asistencia: asi },
            panelClass: 'user-modal-dialog'
        });
        ref.afterClosed().subscribe(updated => {
            if (updated) {
                this.loadSessions();
                this.loadTotals();
            }
        });
    }

    deleteAsistencia(id: number) {
        if (!confirm('¿Eliminar esta asistencia?')) return;
        this.asiSvc.delete(id).subscribe(() => {
            this.loadSessions();
            this.loadTotals();
        });
    }

    openBulkOnly() {
        const ref = this.dialog.open(AsistenciaModalComponent, {
            width: '600px',
            data: { session: null },
            panelClass: 'user-modal-dialog'
        });
        ref.afterClosed().subscribe(updated => {
            if (updated) {
                this.loadSessions();
                this.loadTotals();
            }
        });
    }

}
