import { Component, OnInit, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  ReactiveFormsModule,
  FormBuilder,
  FormGroup,
  Validators
} from '@angular/forms';

import {
  MatDialogModule,
  MatDialogRef,
  MAT_DIALOG_DATA
} from '@angular/material/dialog';
import { MatTabsModule }      from '@angular/material/tabs';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule }     from '@angular/material/input';
import { MatSelectModule }    from '@angular/material/select';
import { MatButtonModule }    from '@angular/material/button';

import {
  BonificacionesService,
  Bonificacion
} from '../../../services/bonificaciones.service';
import {
  CategoriaService,
  Categoria
} from '../../../services/categoria.service';

export interface BonificacionModalData {
  bonificacion?: Bonificacion;
}

@Component({
  selector: 'app-bonificacion-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatTabsModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule
  ],
  templateUrl: './bonificacion-modal.component.html',
  styleUrls: ['./bonificacion-modal.component.css']
})
export class BonificacionModalComponent implements OnInit {
  form!: FormGroup;
  categorias: Categoria[] = [];
  selectedFile: File | null = null;
  isEditMode = false;

  constructor(
    private fb: FormBuilder,
    private bonifSvc: BonificacionesService,
    private catSvc:   CategoriaService,
    private dialogRef: MatDialogRef<BonificacionModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: BonificacionModalData | null
  ) {
    // si no viene data, aseguramos un objeto vacío
    if (!this.data) {
      this.data = {};
    }
  }

  ngOnInit(): void {
    this.isEditMode = !!this.data!.bonificacion;

    // inicializo el formGroup aquí, garantizando que form nunca sea undefined
    this.form = this.fb.group({
      name: [
        this.data!.bonificacion?.name || '',
        Validators.required
      ],
      valor: [
        this.data!.bonificacion?.valor || '',
        Validators.required
      ],
      categoria_id: [
        this.data!.bonificacion?.categoria_id || '',
        Validators.required
      ]
    });

    // cargo categorías para el select
    this.catSvc.getAll().subscribe((cats: Categoria[]) => {
      this.categorias = cats;
    });
  }

  onCreate(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    const fv = this.form.value;
    const action$ = this.isEditMode
      ? this.bonifSvc.update(this.data!.bonificacion!.id, fv)
      : this.bonifSvc.create(fv);

    action$.subscribe(() => this.dialogRef.close(true));
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files?.[0] ?? null;
  }

  onUpload(): void {
    if (!this.selectedFile) return;
    const form = new FormData();
    form.append('file', this.selectedFile);
    this.bonifSvc.bulkUpload(form).subscribe(() => this.dialogRef.close(true));
  }

  cancel(): void {
    this.dialogRef.close(false);
  }
}
