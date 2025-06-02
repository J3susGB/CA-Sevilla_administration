import { Component, OnInit, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';

import { MAT_DIALOG_DATA, MatDialogRef, MatDialogModule } from '@angular/material/dialog';
import { MatTabsModule } from '@angular/material/tabs';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';

import { SancionService, Sancion } from '../../../services/sancion.service';
import { CategoriaService, Categoria } from '../../../services/categoria.service';

@Component({
  selector: 'app-sancion-modal',
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
  templateUrl: './sancion-modal.component.html',
  styleUrls: ['./sancion-modal.component.css']
})
export class SancionModalComponent implements OnInit {
  sancionForm!: FormGroup;
  categories: Categoria[] = [];
  selectedFile: File | null = null;
  isEditMode = false;

  constructor(
    private fb: FormBuilder,
    private sancionSvc: SancionService,
    private catSvc: CategoriaService,
    private dialogRef: MatDialogRef<SancionModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { sancion?: Sancion }
  ) {}

  ngOnInit(): void {
    this.isEditMode = !!this.data?.sancion;

    this.sancionForm = this.fb.group({
      nif: [this.data?.sancion?.nif || '', [Validators.required, Validators.minLength(9), Validators.maxLength(10)]],
      categoria_id: [this.data?.sancion?.categoria_id || '', Validators.required],
      fecha: [this.data?.sancion?.fecha || '', Validators.required],
      tipo: [this.data?.sancion?.tipo || '', Validators.required],
      nota: [this.data?.sancion?.nota || '', [Validators.required, Validators.min(0), Validators.max(10)]]
    });

    this.catSvc.getAll().subscribe(cats => this.categories = cats);
  }

  onCreate(): void {
    if (this.sancionForm.invalid) {
      this.sancionForm.markAllAsTouched();
      return;
    }

    const fv = this.sancionForm.value;

    if (this.isEditMode && this.data.sancion?.id) {
      this.sancionSvc.update(this.data.sancion.id, fv).subscribe(() => this.dialogRef.close(true));
    } else {
      this.sancionSvc.create(fv).subscribe(() => this.dialogRef.close(true));
    }
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files?.[0] ?? null;
  }

  onUpload(): void {
    if (!this.selectedFile) return;
    const form = new FormData();
    form.append('file', this.selectedFile);
    this.sancionSvc.bulkUpload(form).subscribe(() => this.dialogRef.close(true));
  }
}
