// src/app/dashboard-admin/informes/informe-modal/informe-modal.component.ts

import { Component, Inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import {
  MatDialogRef, MAT_DIALOG_DATA, MatDialogModule
} from '@angular/material/dialog';
import { MatTabsModule } from '@angular/material/tabs';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';

import { InformeService, Informe } from '../../../services/informe.service';
import { CategoriaService, Categoria } from '../../../services/categoria.service';

@Component({
  selector: 'app-informe-modal',
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
  templateUrl: './informe-modal.component.html'
})
export class InformeModalComponent implements OnInit {
  form!: FormGroup;
  categories: Categoria[] = [];
  selectedFile: File | null = null;
  isEditMode = false;

  constructor(
    private fb: FormBuilder,
    private infSvc: InformeService,
    private catSvc: CategoriaService,
    private dialogRef: MatDialogRef<InformeModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { informe?: Informe }
  ) {}

  ngOnInit(): void {
    this.isEditMode = !!this.data?.informe;

    // Inicializa el formulario con valores si hay edición
    this.form = this.fb.group({
      nif: [this.data?.informe?.nif || '', Validators.required],
      nota: [this.data?.informe?.nota || '', Validators.required],
      categoria_id: [null, Validators.required],  // <-- Se asignará después
      fecha: [this.data?.informe?.fecha || '', Validators.required],
    });

    // Carga las categorías antes de renderizar
    this.catSvc.getAll().subscribe({
      next: cats => {
        this.categories = cats;

        // En modo edición, sincroniza la categoría seleccionada
        if (this.isEditMode && this.data?.informe?.categoria_id) {
          const catId = this.data.informe.categoria_id;
          const exists = this.categories.some(c => c.id === catId);
          if (exists) {
            this.form.get('categoria_id')?.setValue(catId);
          }
        }
      },
      error: () => {
        console.error('Error al cargar categorías');
      }
    });
  }

  onSubmit(): void {
    if (this.form.invalid) return;

    const data = this.form.value;

    if (this.isEditMode) {
      this.infSvc.update(this.data.informe!.id, data).subscribe(() => this.dialogRef.close(true));
    } else {
      this.infSvc.create(data).subscribe(() => this.dialogRef.close(true));
    }
  }

  onFileSelected(e: Event): void {
    const input = e.target as HTMLInputElement;
    this.selectedFile = input.files?.[0] ?? null;
  }

  onUpload(): void {
    if (!this.selectedFile) return;
    const form = new FormData();
    form.append('file', this.selectedFile);
    this.infSvc.bulkUpload(form).subscribe(() => this.dialogRef.close(true));
  }
}
