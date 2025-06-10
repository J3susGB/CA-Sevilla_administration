import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatDialogModule } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';

import { SancionService, Sancion } from '../../services/sancion.service';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../shared/components/confirm-dialog/confirm-dialog.component';
import { ToastService, Toast } from '../../shared/services/toast.service';
import { AuthService } from '../../services/auth.service';
import { SancionModalComponent } from './sancion-modal/sancion-modal.component';

@Component({
    selector: 'app-sanciones-list',
    standalone: true,
    imports: [
        CommonModule,
        RouterModule,
        ReactiveFormsModule,
        MatDialogModule,
        MatButtonModule,
        MatIconModule,
        MatFormFieldModule,
        MatInputModule,
        ConfirmDialogComponent
    ],
    templateUrl: './sanciones-list.component.html',
    styleUrls: ['./sanciones-list.component.css']
})
export class SancionesListComponent implements OnInit {
    sanciones: Sancion[] = [];
    filtered: Sancion[] = [];
    filterForm!: FormGroup;
    toasts: Toast[] = [];
    backLink = '/';

    constructor(
        private sancionSvc: SancionService,
        private auth: AuthService,
        private dialog: MatDialog,
        private fb: FormBuilder,
        private toastService: ToastService
    ) { }

    ngOnInit(): void {
        const roles = this.auth.getRoles();
        if (!roles.some(r => ['ROLE_ADMIN', 'ROLE_CLASIFICACION', 'ROLE_INFORMACION'].includes(r))) return;

        this.backLink = roles.includes('ROLE_ADMIN') ? '/admin' :
            roles.includes('ROLE_CLASIFICACION') ? '/clasificacion' : '/informacion';

        this.toastService.toasts$.subscribe(t => this.toasts = t);

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
        this.sancionSvc.getAll().subscribe(res => {
            this.sanciones = res.data.sort((a, b) => {
                const A = `${a.first_surname} ${a.second_surname ?? ''} ${a.name}`.trim().toLowerCase();
                const B = `${b.first_surname} ${b.second_surname ?? ''} ${b.name}`.trim().toLowerCase();
                return A.localeCompare(B);
            });

            this.filtered = [...this.sanciones];
            this.applyFilter();
        });
    }

    applyFilter(): void {
        const { nameFilter, catFilter } = this.filterForm.value;
        const nf = nameFilter.trim().toLowerCase();
        const cf = catFilter.trim().toLowerCase();

        this.filtered = this.sanciones.filter(s => {
            const fullName = `${s.first_surname} ${s.second_surname || ''} ${s.name}`.toLowerCase();
            const matchesName = !nf || fullName.includes(nf);
            const matchesCat = !cf || (s.categoria || '').toLowerCase().includes(cf);
            return matchesName && matchesCat;
        });
    }

    add(): void {
        this.dialog.open(SancionModalComponent, {
            width: '500px',
            panelClass: 'user-modal-dialog'
        }).afterClosed().subscribe(done => {
            if (done) {
                this.load();
                this.toastService.show('Sanción añadida con éxito', 'success');
            }
        });
    }

    edit(s: Sancion): void {
        if (!s.id) return;

        this.sancionSvc.get(s.id).subscribe(res => {
            const data = res.data;

            this.dialog.open(SancionModalComponent, {
                width: '500px',
                data: { sancion: data },
                panelClass: 'user-modal-dialog'
            }).afterClosed().subscribe(done => {
                if (done) {
                    this.load();
                    this.toastService.show('Sanción actualizada con éxito', 'success');
                }
            });
        });
    }
    
    delete(s: Sancion): void {
        const title = `¿Eliminar sanción de ${s.first_surname} ${s.second_surname || ''}, ${s.name}?`;
        const data: ConfirmDialogData = {
            title,
            message: '¡Esta acción no se puede deshacer!',
            confirmText: 'Eliminar',
            cancelText: 'Cancelar'
        };

        this.dialog.open(ConfirmDialogComponent, {
            data,
            panelClass: 'confirm-dialog-panel',
            backdropClass: 'confirm-dialog-backdrop'
        }).afterClosed().subscribe(conf => {
            if (!conf) return;

            this.sancionSvc.delete(s.id!).subscribe({
                next: () => {
                    this.load();
                    this.toastService.show('Sanción eliminada con éxito', 'error');
                },
                error: () => this.toastService.show('Error al eliminar', 'error')
            });
        });
    }
}
