import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';

import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';

import { forkJoin } from 'rxjs';
import { ArbitroService, Arbitro } from '../../services/arbitro.service';
import { CategoriaService, Categoria } from '../../services/categoria.service';
import { AuthService } from '../../services/auth.service';
import { ArbitroModalComponent } from './arbitro-modal/arbitro-modal.component';

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
    MatInputModule
  ],
  templateUrl: './arbitros-list.component.html',
  styleUrls: ['./arbitros-list.component.css']
})
export class ArbitrosListComponent implements OnInit {
  arbitros: Arbitro[] = [];
  filteredArbitros: Arbitro[] = [];
  filterForm!: FormGroup;
  private readonly ALL_LIMIT = 1000;

  constructor(
    private arbSvc: ArbitroService,
    private catSvc: CategoriaService,
    private auth: AuthService,
    private dialog: MatDialog,
    private fb: FormBuilder
  ) { }

  ngOnInit(): void {
    // Solo ADMIN y CAPACITACION
    if (!this.auth.getRoles().some(r => ['ROLE_ADMIN', 'ROLE_CAPACITACION'].includes(r))) {
      return;
    }

    // Formulario reactivo de filtros
    this.filterForm = this.fb.group({
      nameFilter: [''],
      catFilter: ['']
    });
    this.filterForm.valueChanges.subscribe(() => this.applyFilter());

    // Carga inicial de datos + categorías
    this.load();
  }

  private load(): void {
    forkJoin({
      cats: this.catSvc.getAll(),
      arb: this.arbSvc.getAll(1, this.ALL_LIMIT)
    }).subscribe(({ cats, arb }) => {
      // 1) Mapeamos id→nombre de categoría
      const mapCat = cats.reduce<Record<number, string>>((acc, c) => {
        acc[c.id] = c.nombre;   // o c.name si lo tienes así
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
    this.dialog.open(ArbitroModalComponent, {
      width: '500px',
      panelClass: 'user-modal-dialog'
    }).afterClosed().subscribe(created => {
      if (created) this.load();
    });
  }

  editArbitro(a: Arbitro): void {
    this.dialog.open(ArbitroModalComponent, {
      width: '500px',
      data: { arbitro: a },
      panelClass: 'user-modal-dialog'
    }).afterClosed().subscribe(done => done && this.load());
  }

  deleteArbitro(a: Arbitro): void {
    if (!confirm(`¿Eliminar árbitro ${a.first_surname} ${a.name}?`)) return;
    this.arbSvc.delete(a.id).subscribe(() => this.load());
  }
}
