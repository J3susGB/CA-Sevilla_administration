// src/app/dashboard-admin/simulacros/simulacros-modal/simulacros-modal.component.ts

import { Component, OnInit, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatTabsModule } from '@angular/material/tabs';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';

import { SimulacrosService, Simulacro } from '../../../services/simulacros.service';
import { CategoriaService, Categoria } from '../../../services/categoria.service';

@Component({
  selector: 'app-simulacros-modal',
  standalone: true,
  templateUrl: './simulacros-modal.component.html',
  styleUrls: ['./simulacros-modal.component.css'],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatTabsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatSelectModule,
    MatDatepickerModule,
    MatNativeDateModule
  ]
})
export class SimulacrosModalComponent implements OnInit {
  createForm!: FormGroup;
  editForm!: FormGroup;
  selectedFile: File | null = null;
  isEditMode = false;
  categorias: Categoria[] = [];

  constructor(
    private fb: FormBuilder,
    private simSvc: SimulacrosService,
    private catSvc: CategoriaService,
    private dialogRef: MatDialogRef<SimulacrosModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { simulacro?: Simulacro }
  ) {}

  ngOnInit(): void {
    this.isEditMode = !!this.data?.simulacro;

    this.catSvc.getAll().subscribe(cats => this.categorias = cats);

    this.createForm = this.fb.group({
      nif: ['', Validators.required],
      categoria_id: ['', Validators.required],
      fecha: ['', Validators.required],
      periodo: ['', Validators.required]
    });

    this.editForm = this.fb.group({
      fecha: [
        this.data?.simulacro?.fecha
          ? this.parseDate(this.data.simulacro.fecha)
          : '',
        Validators.required
      ],
      periodo: [this.data?.simulacro?.periodo ?? 0, Validators.required]
    });
  }

  onCreate(): void {
    if (this.createForm.invalid) return;

    const fv = this.createForm.value;

    const payload = {
      nif: fv.nif.toUpperCase(),
      categoria_id: +fv.categoria_id,
      fecha: this.formatDate(fv.fecha),
      periodo: parseFloat(fv.periodo)
    };

    this.simSvc.create(payload).subscribe(() => this.dialogRef.close(true));
  }

  onUpdate(): void {
    if (!this.data?.simulacro || this.editForm.invalid) return;

    const fv = this.editForm.value;

    const payload = {
      fecha: this.formatDate(fv.fecha),
      periodo: parseFloat(fv.periodo)
    };

    this.simSvc.update(this.data.simulacro.id, payload).subscribe(() => this.dialogRef.close(true));
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files?.[0] ?? null;
  }

  onUpload(): void {
    if (!this.selectedFile) return;

    const form = new FormData();
    form.append('file', this.selectedFile);

    this.simSvc.bulkUpload(form).subscribe(() => this.dialogRef.close(true));
  }

  private formatDate(d: Date): string {
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    return `${dd}-${mm}-${yyyy}`;
  }

  private parseDate(dateStr: string): Date | null {
    const [dd, mm, yyyy] = dateStr.split('-').map(Number);
    return new Date(yyyy, mm - 1, dd);
  }
}
