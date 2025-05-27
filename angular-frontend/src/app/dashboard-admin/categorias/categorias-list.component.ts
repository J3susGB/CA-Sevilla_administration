// src/app/dashboard-admin/categorias/categorias-list.component.ts

import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

import {
    ConfirmDialogComponent,
    ConfirmDialogData
} from '../../shared/components/confirm-dialog/confirm-dialog.component';

import { CategoriaService, Categoria } from '../../services/categoria.service';
import { AuthService } from '../../services/auth.service';
import { CategoriaModalComponent } from './categoria-modal.component';

import { RouterModule, Router } from '@angular/router';

import { ToastService, Toast } from '../../shared/services/toast.service';

@Component({
    selector: 'app-categorias-list',
    standalone: true,
    imports: [
        CommonModule,
        ReactiveFormsModule,
        MatDialogModule,
        MatFormFieldModule,
        MatInputModule,
        MatButtonModule,
        MatIconModule,
        CategoriaModalComponent,
        ConfirmDialogComponent
    ],
    templateUrl: './categorias-list.component.html',
    styleUrls: ['./categorias-list.component.css']
})
export class CategoriasListComponent implements OnInit {
    categorias: Categoria[] = [];
    filteredCategorias: Categoria[] = [];
    filterForm!: FormGroup;
    toasts: Toast[] = [];

    constructor(
        private catSvc: CategoriaService,
        private auth: AuthService,
        private dialog: MatDialog,
        private fb: FormBuilder,
        private router: Router,
        private toastService: ToastService
    ) { }

    ngOnInit(): void {
        // Solo ADMIN y CAPACITACION
    if (!this.auth.getRoles().some(r => ['ROLE_ADMIN', 'ROLE_CAPACITACION'].includes(r))) {
      return;
    }

        this.toastService.toasts$.subscribe((toasts: Toast[]) => {
            this.toasts = toasts;
        });

        this.filterForm = this.fb.group({ nombreFilter: [''] });
        this.filterForm.valueChanges.subscribe(() => this.applyFilter());
        this.load();
    }

    private load(): void {
        this.catSvc.getAll().subscribe({
            next: (cats) => {
                // Ordenamos alfabéticamente por nombre antes de asignar
                this.categorias = cats.sort((a, b) =>
                    a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' })
                );
                this.filteredCategorias = [...this.categorias];
                this.applyFilter();
            },
            error: (err) => console.error('Error cargando categorías', err)
        });
    }

    private applyFilter(): void {
        const f = this.filterForm.value.nombreFilter.trim().toLowerCase();
        this.filteredCategorias = this.categorias.filter(c =>
            !f || c.nombre.toLowerCase().includes(f)
        );
    }

    addCategoria(): void {
        this.dialog.open(CategoriaModalComponent, {
            width: '400px',
            panelClass: 'user-modal-dialog'
        })
            .afterClosed()
            .subscribe(created => {
                if (created) {
                    this.load();
                    this.toastService.show('Añadida con éxito ✅ ', 'success');
                }
            });
    }

    editCategoria(c: Categoria): void {
        this.dialog.open(CategoriaModalComponent, {
            width: '400px',
            data: { categoria: c },
            panelClass: 'user-modal-dialog'
        })
            .afterClosed()
            .subscribe(updated => {
                if (updated) {
                    this.load();
                    this.toastService.show('Actualizada con éxito ✅ ', 'success');
                }
            });

    }

    deleteCategoria(c: Categoria): void {
        const data: ConfirmDialogData = {
            title: `¿Eliminar ${c.nombre}?`,
            message: '¡Esta acción no se puede deshacer!',
            confirmText: 'Eliminar',
            cancelText: 'Cancelar'
        };

        this.dialog.open(ConfirmDialogComponent, {
            data,
            panelClass: 'confirm-dialog-panel',   
            backdropClass: 'confirm-dialog-backdrop' 
        })
            .afterClosed()
            .subscribe(confirmed => {
                if (!confirmed) return;
                this.catSvc.delete(c.id!).subscribe({
                    next: () => {
                        this.load();
                        this.toastService.show('Eliminada con éxito 🗑️', 'error');
                    },
                    error: () => {
                        this.toastService.show('Error al eliminar categoría ❌', 'error');
                    }
                });
            });
    }


    /** Navegar atrás al dashboard admin */
    goBack(): void {
        this.router.navigate(['/admin']);
    }
}
