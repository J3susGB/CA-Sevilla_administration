// src/app/dashboard-clasificacion/fisica/fisica-list.component.ts

import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { forkJoin } from 'rxjs';

import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';

import { ConfirmDialogComponent, ConfirmDialogData } from '../../shared/components/confirm-dialog/confirm-dialog.component';
import { FisicaService, Fisica } from '../../services/fisica.service';
import { CategoriaService } from '../../services/categoria.service';
import { AuthService } from '../../services/auth.service';
import { ToastService, Toast } from '../../shared/services/toast.service';
import { FisicaModalComponent } from './fisica-modal/fisica-modal.component';

@Component({
  selector: 'app-fisica-list',
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
  templateUrl: './fisica-list.component.html',
  styleUrls: ['./fisica-list.component.css']
})
export class FisicaListComponent implements OnInit {
  fisicas: Fisica[] = [];
  filteredFisicas: Fisica[] = [];
  filterForm!: FormGroup;
  toasts: Toast[] = [];
  backLink = '/';
  private readonly ALL_LIMIT = 1000;

  constructor(
    private fisicaSvc: FisicaService,
    private catSvc: CategoriaService,
    private auth: AuthService,
    private dialog: MatDialog,
    private fb: FormBuilder,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    const roles = this.auth.getRoles();
    if (roles.includes('ROLE_ADMIN')) {
      this.backLink = '/admin';
    } else if (roles.includes('ROLE_CLASIFICACION')) {
      this.backLink = '/clasificacion';
    }

    this.toastService.toasts$.subscribe((toasts: Toast[]) => {
      this.toasts = toasts;
    });

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
      cats: this.catSvc.getAll(),
      fisicas: this.fisicaSvc.getAll()
    }).subscribe(({ cats, fisicas }) => {
      const mapCat = cats.reduce<Record<number, string>>((acc, c) => {
        acc[c.id] = c.nombre;
        return acc;
      }, {});

      this.fisicas = fisicas.data.map(f => ({
        ...f,
        categoria: mapCat[f.categoria_id] || '—'
      })).sort((a, b) => {
        const A = `${a.first_surname} ${a.second_surname ?? ''} ${a.name}`.trim().toLowerCase();
        const B = `${b.first_surname} ${b.second_surname ?? ''} ${b.name}`.trim().toLowerCase();
        return A.localeCompare(B);
      });

      this.filteredFisicas = [...this.fisicas];
      this.applyFilter();
    }, err => {
      console.error('Error cargando notas físicas o categorías', err);
    });
  }

  applyFilter(): void {
    const { nameFilter, catFilter } = this.filterForm.value;
    const nf = nameFilter.trim().toLowerCase();
    const cf = catFilter.trim().toLowerCase();

    this.filteredFisicas = this.fisicas.filter(f => {
      const fullName = `${f.first_surname} ${f.second_surname || ''} ${f.name}`.toLowerCase();
      const matchesName = !nf || fullName.includes(nf);
      const matchesCat = !cf || (f.categoria || '').toLowerCase().includes(cf);
      return matchesName && matchesCat;
    });
  }

  addFisica(): void {
    this.dialog.open(FisicaModalComponent, {
      width: '600px',
      panelClass: 'user-modal-dialog'
    }).afterClosed().subscribe(created => {
      if (created) {
        this.load();
        this.toastService.show('Nota física añadida ✅', 'success');
      }
    });
  }

  editFisica(f: Fisica): void {
    this.dialog.open(FisicaModalComponent, {
      width: '600px',
      data: { fisica: f },
      panelClass: 'user-modal-dialog'
    }).afterClosed().subscribe(updated => {
      if (updated) {
        this.load();
        this.toastService.show('Nota física actualizada ✅', 'success');
      }
    });
  }

  deleteFisica(f: Fisica): void {
    const title = `Eliminar nota de ${f.first_surname} ${f.second_surname ?? ''}, ${f.name}?`;

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
    }).afterClosed().subscribe(confirmed => {
      if (!confirmed) return;

      this.fisicaSvc.delete(f.id).subscribe({
        next: () => {
          this.load();
          this.toastService.show('Nota física eliminada 🗑️', 'error');
        },
        error: () => {
          this.toastService.show('Error al eliminar nota ❌', 'error');
        }
      });
    });
  }
}
