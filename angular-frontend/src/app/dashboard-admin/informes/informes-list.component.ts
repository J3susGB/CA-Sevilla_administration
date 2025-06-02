// src/app/dashboard-admin/informes/informes-list.component.ts
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


import { InformeService, Informe } from '../../services/informe.service';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../shared/components/confirm-dialog/confirm-dialog.component';
import { ToastService, Toast } from '../../shared/services/toast.service';
import { InformeModalComponent } from './informe-modal/informe-modal.component';
import { AuthService } from '../../services/auth.service';

@Component({
    selector: 'app-informes-list',
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
        ConfirmDialogComponent // si lo usas
    ],
    templateUrl: './informes-list.component.html',
    styleUrls: ['./informes-list.component.css']
})
export class InformesListComponent implements OnInit {
    informes: Informe[] = [];
    filteredInformes: Informe[] = [];
    filterForm!: FormGroup;
    toasts: Toast[] = [];
    backLink = '/';

    constructor(
        private infSvc: InformeService,
        private auth: AuthService,
        private fb: FormBuilder,
        private dialog: MatDialog,
        private toastService: ToastService
    ) { }

    ngOnInit(): void {

        const roles = this.auth.getRoles();
        if (!roles.some(r => ['ROLE_ADMIN', 'ROLE_CLASIFICACION', 'ROLE_INFORMACION'].includes(r))) return;

        this.backLink = roles.includes('ROLE_ADMIN') ? '/informacion' :
            roles.includes('ROLE_CLASIFICACION') ? '/clasificacion' : '/informacion';
        this.toastService.toasts$.subscribe(toasts => this.toasts = toasts);

        this.filterForm = this.fb.group({
            nameFilter: [''],
            catFilter: ['']
        });

        this.filterForm.valueChanges.subscribe(() => this.applyFilter());
        this.load();

    }

    private load(): void {
        this.infSvc.getAll().subscribe({
            next: res => {
                this.informes = res.data
                    .sort((a, b) => {
                        const fechaA = this.parseDate(a.fecha);
                        const fechaB = this.parseDate(b.fecha);
                        return fechaA.getTime() - fechaB.getTime(); // ascendente (antigua → reciente)
                    });

                this.filteredInformes = [...this.informes];
                this.applyFilter();
            },
            error: () => {
                console.error('Error al cargar informes');
            }
        });
    }

    private parseDate(fechaStr: string): Date {
        const [day, month, year] = fechaStr.split('-').map(Number);
        return new Date(year, month - 1, day);
    }

    applyFilter(): void {
        const { nameFilter, catFilter } = this.filterForm.value;
        const nf = nameFilter.toLowerCase();
        const cf = catFilter.toLowerCase();

        this.filteredInformes = this.informes.filter(i => {
            const fullName = `${i.first_surname} ${i.second_surname ?? ''} ${i.name}`.toLowerCase();
            return fullName.includes(nf) && i.categoria.toLowerCase().includes(cf);
        });
    }

    addInforme(): void {
        this.dialog.open(InformeModalComponent, {
            width: '500px',
            panelClass: 'user-modal-dialog'
        }).afterClosed().subscribe(success => {
            if (success) {
                this.load();
                this.toastService.show('Informe creado ✅', 'success');
            }
        });
    }

    editInforme(inf: Informe): void {
        this.dialog.open(InformeModalComponent, {
            width: '500px',
            data: { informe: inf },
            panelClass: 'user-modal-dialog'
        }).afterClosed().subscribe(success => {
            if (success) {
                this.load();
                this.toastService.show('Informe actualizado ✅', 'success');
            }
        });
    }

    deleteInforme(inf: Informe): void {
        const surnames = [inf.first_surname, inf.second_surname].filter(Boolean).join(' ');
        const title = `Eliminar informe de ${surnames}, ${inf.name}?`;

        const data: ConfirmDialogData = {
            title,
            message: 'Esta acción no se puede deshacer',
            confirmText: 'Eliminar',
            cancelText: 'Cancelar'
        };

        this.dialog.open(ConfirmDialogComponent, {
            data,
            panelClass: 'confirm-dialog-panel',
            backdropClass: 'confirm-dialog-backdrop'
        }).afterClosed().subscribe(confirmed => {
            if (!confirmed) return;

            this.infSvc.delete(inf.id).subscribe({
                next: () => {
                    this.load();
                    this.toastService.show('Informe eliminado 🗑️', 'error');
                },
                error: () => {
                    this.toastService.show('Error al eliminar informe ❌', 'error');
                }
            });
        });
    }
}
