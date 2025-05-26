// src/app/dashboard-admin/categorias/categoria-modal.component.ts

import { Component, OnInit, Inject }       from '@angular/core';
import { CommonModule }                     from '@angular/common';
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
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule }     from '@angular/material/input';
import { MatButtonModule }    from '@angular/material/button';

import { CategoriaService, Categoria } from '../../services/categoria.service';

@Component({
  selector: 'app-categoria-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule
  ],
  templateUrl: './categoria-modal.component.html',
  styleUrls: ['./categoria-modal.component.css']
})
export class CategoriaModalComponent implements OnInit {
  categoriaForm!: FormGroup;
  private isEditMode = false;

  constructor(
    private fb:        FormBuilder,
    private catSvc:    CategoriaService,
    public dialogRef: MatDialogRef<CategoriaModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { categoria?: Categoria }
  ) {}

  /** Para usar en la plantilla */
  get isEdit(): boolean { return this.isEditMode; }
  get form():    FormGroup { return this.categoriaForm; }

  ngOnInit(): void {
    this.isEditMode = !!this.data?.categoria;
    this.categoriaForm = this.fb.group({
      nombre: [
        this.data?.categoria?.nombre || '',
        Validators.required
      ]
    });
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    const payload = this.form.value;
    const obs = this.isEdit
      ? this.catSvc.update(this.data.categoria!.id, payload)
      : this.catSvc.create(payload);

    obs.subscribe(() => this.dialogRef.close(true));
  }
}
