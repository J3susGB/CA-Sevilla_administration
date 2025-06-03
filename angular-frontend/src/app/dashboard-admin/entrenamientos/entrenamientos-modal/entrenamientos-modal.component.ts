// src/app/dashboard-admin/entrenamientos/entrenamientos-modal/entrenamientos-modal.component.ts

import { Component, OnInit, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatTabsModule } from '@angular/material/tabs';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';

import { EntrenamientosService, Entrenamiento } from '../../../services/entrenamientos.service';

@Component({
  selector: 'app-entrenamientos-modal',
  standalone: true,
  templateUrl: './entrenamientos-modal.component.html',
  styleUrls: ['./entrenamientos-modal.component.css'],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatTabsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule
  ]
})
export class EntrenamientosModalComponent implements OnInit {
  entForm!: FormGroup;
  createForm!: FormGroup;
  selectedFile: File | null = null;
  isEditMode = false;

  constructor(
    private fb: FormBuilder,
    private entSvc: EntrenamientosService,
    private dialogRef: MatDialogRef<EntrenamientosModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { entrenamiento?: Entrenamiento }
  ) {}

  ngOnInit(): void {
    this.isEditMode = !!this.data?.entrenamiento;

    this.entForm = this.fb.group({
      septiembre: [this.data?.entrenamiento?.septiembre || 0, [Validators.min(0)]],
      octubre:    [this.data?.entrenamiento?.octubre    || 0, [Validators.min(0)]],
      noviembre:  [this.data?.entrenamiento?.noviembre  || 0, [Validators.min(0)]],
      diciembre:  [this.data?.entrenamiento?.diciembre  || 0, [Validators.min(0)]],
      enero:      [this.data?.entrenamiento?.enero      || 0, [Validators.min(0)]],
      febrero:    [this.data?.entrenamiento?.febrero    || 0, [Validators.min(0)]],
      marzo:      [this.data?.entrenamiento?.marzo      || 0, [Validators.min(0)]],
      abril:      [this.data?.entrenamiento?.abril      || 0, [Validators.min(0)]],
    });

    this.createForm = this.fb.group({
      nif: ['', [Validators.required]],
      categoria_id: ['', [Validators.required]]
    });
  }

  onUpdate(): void {
    if (this.entForm.invalid || !this.data.entrenamiento) return;

    this.entSvc.update(this.data.entrenamiento.id, this.entForm.value)
      .subscribe(() => this.dialogRef.close(true));
  }

  onCreate(): void {
    if (this.createForm.invalid) return;

    const payload = {
      nif: this.createForm.value.nif.toUpperCase(),
      categoria_id: this.createForm.value.categoria_id
    };

    this.entSvc.create(payload).subscribe(() => this.dialogRef.close(true));
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files?.[0] ?? null;
  }

  onUpload(): void {
    if (!this.selectedFile) return;

    const form = new FormData();
    form.append('file', this.selectedFile);

    this.entSvc.bulkUpload(form).subscribe(() => this.dialogRef.close(true));
  }
}
