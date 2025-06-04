// src/app/dashboard-clasificacion/fisica/fisica-modal/fisica-modal.component.ts

import { Component, OnInit, Inject, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatTabsModule } from '@angular/material/tabs';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';

import { CategoriaService, Categoria } from '../../../services/categoria.service';
import { FisicaService, Fisica } from '../../../services/fisica.service';

@Component({
  selector: 'app-fisica-modal',
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
  templateUrl: './fisica-modal.component.html',
  styleUrls: ['./fisica-modal.component.css']
})
export class FisicaModalComponent implements OnInit {
  fisicaForm!: FormGroup;
  categories: Categoria[] = [];
  selectedFile: File | null = null;
  isEditMode = false;

  @ViewChild('fileInput') fileInput!: ElementRef<HTMLInputElement>;

  constructor(
    private fb: FormBuilder,
    private fisicaSvc: FisicaService,
    private catSvc: CategoriaService,
    private dialogRef: MatDialogRef<FisicaModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { fisica?: Fisica }
  ) {}

  ngOnInit(): void {
    this.isEditMode = !!this.data?.fisica;

    this.fisicaForm = this.fb.group({
      nif: [this.data?.fisica?.nif || '', Validators.required],
      categoria_id: [this.data?.fisica?.categoria_id || '', Validators.required],
      convocatoria: [this.data?.fisica?.convocatoria || '', Validators.required],
      repesca: [this.data?.fisica?.repesca || false],
      yoyo: [this.data?.fisica?.yoyo || '', Validators.required],
      velocidad: [this.data?.fisica?.velocidad ?? null]
    });

    this.catSvc.getAll().subscribe(cats => this.categories = cats);
  }

  onCreate(): void {
    if (this.fisicaForm.invalid) {
      this.fisicaForm.markAllAsTouched();
      return;
    }

    const payload = this.fisicaForm.value;
    if (this.isEditMode) {
      this.fisicaSvc.update(this.data.fisica!.id, payload)
        .subscribe(() => this.dialogRef.close(true));
    } else {
      this.fisicaSvc.create(payload)
        .subscribe(() => this.dialogRef.close(true));
    }
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files?.length) {
      this.selectedFile = input.files[0];
    } else {
      this.selectedFile = null;
    }
  }

  onUpload(): void {
    if (!this.selectedFile) return;

    const form = new FormData();
    form.append('file', this.selectedFile);
    console.log('Enviando archivo:', this.selectedFile);

    this.fisicaSvc.bulkUpload(form).subscribe({
      next: (res) => {
        console.log('Subida exitosa:', res);
        this.clearFileInput();
        this.dialogRef.close(true);
      },
      error: (err) => {
        console.error('Error al subir el archivo:', err);
        this.clearFileInput();
      }
    });
  }

  private clearFileInput(): void {
    this.selectedFile = null;
    if (this.fileInput?.nativeElement) {
      this.fileInput.nativeElement.value = '';
    }
  }
}
