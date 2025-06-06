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
import { MatTabsModule } from '@angular/material/tabs';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';

import { ArbitroService, Arbitro } from '../../../services/arbitro.service';
import { CategoriaService, Categoria } from '../../../services/categoria.service';

@Component({
  selector: 'app-arbitro-modal',
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
  templateUrl: './arbitro-modal.component.html',
  styleUrls: ['./arbitro-modal.component.css']
})
export class ArbitroModalComponent implements OnInit {
  arbitroForm!: FormGroup;
  categories: Categoria[] = [];
  selectedFile: File | null = null;
  isEditMode = false;

  constructor(
    private fb: FormBuilder,
    private arbSvc: ArbitroService,
    private catSvc: CategoriaService,
    private dialogRef: MatDialogRef<ArbitroModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { arbitro?: Arbitro }
  ) { }

  ngOnInit(): void {
    // Modo edición si recibimos arbitro
    this.isEditMode = !!this.data?.arbitro;

    // Inicializar formulario
    this.arbitroForm = this.fb.group({
      nif: [
        this.data?.arbitro?.nif || '',
        [Validators.required, Validators.minLength(9), Validators.maxLength(10)]
      ],
      name: [
        this.data?.arbitro?.name || '',
        Validators.required
      ],
      first_surname: [
        this.data?.arbitro?.first_surname || '',
        Validators.required
      ],
      second_surname: [
        this.data?.arbitro?.second_surname || ''
      ],
      sexo: [
        this.data?.arbitro?.sexo || '',
        Validators.required
      ],
      categoria_id: [
        this.data?.arbitro?.categoria_id || '',
        Validators.required
      ]
    });

    // Cargar categorías para el select
    this.catSvc.getAll().subscribe(cats => this.categories = cats);
  }

  onCreate(): void {
    if (this.arbitroForm.invalid) {
      this.arbitroForm.markAllAsTouched();
      return;
    }
    const fv = this.arbitroForm.value;

    if (this.isEditMode) {
      // Actualizar
      this.arbSvc.update(this.data.arbitro!.id, fv)
        .subscribe(() => this.dialogRef.close(true));
    } else {
      // Crear
      this.arbSvc.create(fv)
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
    this.arbSvc.bulkUpload(form).subscribe(() => this.dialogRef.close(true));
  }
}
