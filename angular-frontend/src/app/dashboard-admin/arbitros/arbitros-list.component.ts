import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';

import { forkJoin } from 'rxjs';

// Angular Material
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';

// Diálogo de confirmación
import {
  ConfirmDialogComponent,
  ConfirmDialogData
} from '../../shared/components/confirm-dialog/confirm-dialog.component';

import { ArbitroService, Arbitro } from '../../services/arbitro.service';
import { CategoriaService, Categoria } from '../../services/categoria.service';
import { AuthService } from '../../services/auth.service';
import { ArbitroModalComponent } from './arbitro-modal/arbitro-modal.component';

import { ToastService, Toast } from '../../shared/services/toast.service';

@Component({
  selector: 'app-arbitros-list',
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
  templateUrl: './arbitros-list.component.html',
  styleUrls: ['./arbitros-list.component.css']
})
export class ArbitrosListComponent implements OnInit {
  arbitros: Arbitro[] = [];
  filteredArbitros: Arbitro[] = [];
  filterForm!: FormGroup;
  private readonly ALL_LIMIT = 1000;
  toasts: Toast[] = [];
  backLink = '/';  // ruta por defecto

  constructor(
    private arbSvc: ArbitroService,
    private catSvc: CategoriaService,
    private auth: AuthService,
    private dialog: MatDialog,
    private fb: FormBuilder,
    private toastService: ToastService
  ) { }

  ngOnInit(): void {
    // Solo ADMIN y CAPACITACION
    if (!this.auth.getRoles().some(r => ['ROLE_ADMIN', 'ROLE_CAPACITACION'].includes(r))) {
      return;
    }

    // Calcula la ruta “atrás” según rol
    const roles = this.auth.getRoles();
    if (roles.includes('ROLE_ADMIN')) {
      this.backLink = '/admin';
    } else if (roles.includes('ROLE_CAPACITACION')) {
      this.backLink = '/capacitacion';
    }

    this.toastService.toasts$.subscribe((toasts: Toast[]) => {
      this.toasts = toasts;
    });

    // Formulario reactivo de filtros
    this.filterForm = this.fb.group({
      nameFilter: [''],
      catFilter: ['']
    });
    this.filterForm.valueChanges.subscribe(() => this.applyFilter());

    // Carga inicial de datos + categorías
    this.load();
  }

  dismissToast(id: number) {
    this.toastService.removeToast(id);
  }

  private load(): void {
    forkJoin({
      cats: this.catSvc.getAll(),
      arb: this.arbSvc.getAll(1, this.ALL_LIMIT)
    }).subscribe(({ cats, arb }) => {
      // 1) Mapeamos id→nombre de categoría
      const mapCat = cats.reduce<Record<number, string>>((acc, c) => {
        acc[c.id] = c.nombre;   // 
        return acc;
      }, {});

      // 2) Preparamos el array y lo ordenamos A→Z según first_surname, second_surname, name
      this.arbitros = arb.data
        .map(a => ({
          ...a,
          categoria_name: mapCat[a.categoria_id] || '—'
        }))
        .sort((a, b) => {
          // concatenamos para comparar en un solo paso
          const A = `${a.first_surname} ${a.second_surname ?? ''} ${a.name}`.trim().toLowerCase();
          const B = `${b.first_surname} ${b.second_surname ?? ''} ${b.name}`.trim().toLowerCase();
          return A.localeCompare(B);
        });

      // 3) Inicializamos el filtrado
      this.filteredArbitros = [...this.arbitros];
      this.applyFilter();
    }, err => {
      console.error('Error cargando árbitros o categorías', err);
    });
  }

  applyFilter(): void {
    const { nameFilter, catFilter } = this.filterForm.value;
    const nf = nameFilter.trim().toLowerCase();
    const cf = catFilter.trim().toLowerCase();

    this.filteredArbitros = this.arbitros.filter(a => {
      const fullName = `${a.first_surname} ${a.second_surname || ''} ${a.name}`.toLowerCase();
      const matchesName = !nf || fullName.includes(nf);
      const matchesCat = !cf || (a.categoria_name || '').toLowerCase().includes(cf);
      return matchesName && matchesCat;
    });
  }

  addArbitro(): void {
    this.dialog
      .open(ArbitroModalComponent, {
        width: '500px',
        panelClass: 'user-modal-dialog'
      })
      .afterClosed()
      .subscribe(created => {
        if (created) {
          this.load();
          this.toastService.show('Arbitro añadido con éxito', 'success');
        }
      });
  }


  editArbitro(a: Arbitro): void {
    this.dialog
      .open(ArbitroModalComponent, {
        width: '500px',
        data: { arbitro: a },
        panelClass: 'user-modal-dialog'
      })
      .afterClosed()
      .subscribe(done => {
        if (done) {
          this.load();
          this.toastService.show('Aarbitro actualizado con éxito', 'success');
        }
      });
  }


  deleteArbitro(a: Arbitro): void {
    // Construimos la parte de apellidos (uno o dos)
    const surnames = [a.first_surname, a.second_surname]
      .filter(part => !!part)      // elimina null, undefined o ''
      .join(' ');                  // une con espacio: "García López" o "García"

    // Luego añadimos la coma y el nombre
    const title = `Eliminar a ${surnames}, ${a.name}?`;

    const data: ConfirmDialogData = {
      title,
      message: '¡Esta acción no se puede deshacer!',
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

        this.arbSvc.delete(a.id).subscribe({
          next: () => {
            this.load();
            this.toastService.show('Árbitro eliminado con éxito', 'error');
          },
          error: () => {
            this.toastService.show('Error al eliminar árbitro', 'error');
          }
        });
      });
  }


}
