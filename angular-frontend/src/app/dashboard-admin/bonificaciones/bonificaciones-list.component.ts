import { Component, OnInit } from '@angular/core';
import { CommonModule }        from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatFormFieldModule }  from '@angular/material/form-field';
import { MatInputModule }      from '@angular/material/input';
import { MatButtonModule }     from '@angular/material/button';
import { MatIconModule }       from '@angular/material/icon';
import { RouterModule, Router } from '@angular/router';

import { forkJoin } from 'rxjs';
import { map }      from 'rxjs/operators';

import {
  ConfirmDialogComponent,
  ConfirmDialogData
} from '../../shared/components/confirm-dialog/confirm-dialog.component';

import { ToastService, Toast }           from '../../shared/services/toast.service';
import { AuthService }                   from '../../services/auth.service';
import {
  BonificacionesService,
  Bonificacion
} from '../../services/bonificaciones.service';
import {
  CategoriaService,
  Categoria
} from '../../services/categoria.service';

// **IMPORT EXACTO** según tu estructura:
import { BonificacionModalComponent }
  from './bonificacion-modal/bonificacion-modal.component';

@Component({
  selector: 'app-bonificaciones-list',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    RouterModule,
    BonificacionModalComponent,
    ConfirmDialogComponent
  ],
  templateUrl: './bonificaciones-list.component.html',
  styleUrls: ['./bonificaciones-list.component.css']
})
export class BonificacionesListComponent implements OnInit {
  bonificaciones: (Bonificacion & { categoria_name: string })[] = [];
  filteredBonificaciones: (Bonificacion & { categoria_name: string })[] = [];
  filterForm!: FormGroup;
  toasts: Toast[] = [];
  backLink = '/';

  constructor(
    private bonifSvc: BonificacionesService,
    private catSvc:   CategoriaService,
    private auth:     AuthService,
    private dialog:   MatDialog,
    private fb:       FormBuilder,
    private router:   Router,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    if (!this.auth.getRoles().some(r => ['ROLE_ADMIN','ROLE_CAPACITACION'].includes(r))) {
      return;
    }
    const roles = this.auth.getRoles();
    this.backLink = roles.includes('ROLE_ADMIN') ? '/admin' : '/capacitacion';

    this.toastService.toasts$.subscribe(t => this.toasts = t);

    this.filterForm = this.fb.group({
      nameFilter:     [''],
      categoryFilter: ['']
    });
    this.filterForm.valueChanges.subscribe(() => this.applyFilter());

    this.load();
  }

  private load(): void {
    forkJoin({
      bon: this.bonifSvc.getAll().pipe(map(r => r.data)),
      cat: this.catSvc.getAll()
    }).subscribe(({ bon, cat }: { bon: Bonificacion[]; cat: Categoria[] }) => {
      const lookup = new Map<number,string>();
      cat.sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'))
         .forEach(c => lookup.set(c.id!, c.nombre));

      this.bonificaciones = bon.map(b => ({
        ...b,
        categoria_name: lookup.get(b.categoria_id) || ''
      }));
      this.filteredBonificaciones = [...this.bonificaciones];
      this.applyFilter();
    });
  }

  private applyFilter(): void {
    const { nameFilter, categoryFilter } = this.filterForm.value;
    const nf = nameFilter.trim().toLowerCase();
    const cf = categoryFilter.trim().toLowerCase();
    this.filteredBonificaciones = this.bonificaciones.filter(b =>
      (!nf || b.name.toLowerCase().includes(nf)) &&
      (!cf || b.categoria_name.toLowerCase().includes(cf))
    );
  }

  addBonificacion(): void {
    console.log('✨ addBonificacion llamado');  // <-- mira en la consola del navegador
    this.dialog.open(BonificacionModalComponent, {
      width: '600px',
      panelClass: 'user-modal-dialog'
    }).afterClosed().subscribe(created => {
      console.log('✨ modal cerrado, created =', created);
      if (created) {
        this.toastService.show('Bonificación añadida ✅', 'success');
        this.load();
      }
    });
  }

  editBonificacion(b: Bonificacion & { categoria_name: string }): void {
    this.dialog.open(BonificacionModalComponent, {
      width: '600px',
      data: { bonificacion: b },
      panelClass: 'user-modal-dialog'
    }).afterClosed().subscribe(updated => {
      if (updated) {
        this.toastService.show('Bonificación actualizada ✅', 'success');
        this.load();
      }
    });
  }

  deleteBonificacion(b: Bonificacion & { categoria_name: string }): void {
    const data: ConfirmDialogData = {
      title: `¿Eliminar ${b.name}?`,
      message: '¡Esta acción no se puede deshacer!',
      confirmText: 'Eliminar',
      cancelText: 'Cancelar'
    };
    this.dialog.open(ConfirmDialogComponent, {
      data,
      panelClass: 'confirm-dialog-panel',
      backdropClass: 'confirm-dialog-backdrop'
    }).afterClosed().subscribe(confirmed => {
      if (!confirmed) return;
      this.bonifSvc.delete(b.id).subscribe({
        next: () => {
          this.toastService.show('Bonificación eliminada 🗑️', 'error');
          this.load();
        },
        error: () => {
          this.toastService.show('Error al eliminar bonificación ❌', 'error');
        }
      });
    });
  }

  goBack(): void {
    this.router.navigate([this.backLink]);
  }
}
