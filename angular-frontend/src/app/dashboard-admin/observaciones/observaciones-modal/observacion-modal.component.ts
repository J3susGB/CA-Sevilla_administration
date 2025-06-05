// src/app/dashboard-clasificacion/observaciones/observacion-modal.component.ts
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

import { CategoriaService, Categoria } from '../../../services/categoria.service'; 
import { ObservacionService, Observacion } from '../../../services/observacion.service'; 

@Component({
  selector: 'app-observacion-modal',
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
  templateUrl: './observacion-modal.component.html',
  styleUrls: ['./observacion-modal.component.css']
})
export class ObservacionModalComponent implements OnInit {
  observacionForm!: FormGroup;
  categories: Categoria[] = [];
  selectedFile: File | null = null;
  isEditMode = false;

  constructor(
    private fb: FormBuilder,
    private catSvc: CategoriaService,
    private obsSvc: ObservacionService,
    private dialogRef: MatDialogRef<ObservacionModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { observacion?: Observacion }
  ) {}

  ngOnInit(): void {
    this.isEditMode = !!this.data?.observacion;

    this.observacionForm = this.fb.group({
      codigo: [
        this.data?.observacion?.codigo || '',
        [Validators.required, Validators.maxLength(10)]
      ],
      descripcion: [
        this.data?.observacion?.descripcion || '',
        Validators.required
      ],
      categoria_id: [
        this.data?.observacion?.categoria_id || '',
        Validators.required
      ]
    });

    this.catSvc.getAll().subscribe(cats => this.categories = cats);
  }

  onCreate(): void {
    if (this.observacionForm.invalid) {
      this.observacionForm.markAllAsTouched();
      return;
    }

    const body = this.observacionForm.value;

    if (this.isEditMode) {
      this.obsSvc.update(this.data.observacion!.id, body)
        .subscribe(() => this.dialogRef.close(true));
    } else {
      this.obsSvc.create(body)
        .subscribe(() => this.dialogRef.close(true));
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
    this.obsSvc.bulkUpload(form).subscribe(() => this.dialogRef.close(true));
  }
}
