// src/app/dashboard-admin/tests/tests-modal/tests-modal.component.ts
import { Component, Inject, OnInit } from '@angular/core';
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
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule }     from '@angular/material/input';
import { MatSelectModule }    from '@angular/material/select';
import { MatButtonModule }    from '@angular/material/button';
import { MatTabsModule }      from '@angular/material/tabs';

import { TestService } from '../../../services/test.service';
import { CategoriaService, Categoria } from '../../../services/categoria.service';

@Component({
  selector: 'app-tests-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatTabsModule
  ],
  templateUrl: './tests-modal.component.html',
  styleUrls:   ['./tests-modal.component.css']
})
export class TestModalComponent implements OnInit {
  form!: FormGroup;
  categories: Categoria[] = [];
  isEdit = false;
  isBulk = false;
  selectedFile: File | null = null;

  constructor(
    private fb: FormBuilder,
    private testSvc: TestService,
    private catSvc: CategoriaService,
    private dialogRef: MatDialogRef<TestModalComponent>,
    @Inject(MAT_DIALOG_DATA)
    public data: {
      session: {
        id: number;
        fecha: string;
        testNumber: number;
        categoria: string;
        categoria_id: number;
      } | null;
      nota?: {
        id?: number;
        arbitro_id: number;
        categoria_id: number;
        nif: string;
        nota: number;
      };
    }
  ) {
    this.isEdit = !!data.nota;
    this.isBulk = data.session === null;
  }

  ngOnInit(): void {
    // Cargamos categorías para el select
    this.catSvc.getAll().subscribe(cs => this.categories = cs);

    // Si es carga masiva, no montamos el formulario individual
    if (this.isBulk) {
      return;
    }

    // Montamos formulario para editar/crear nota individual
    this.form = this.fb.group({
      fecha: [
        this.data.session!.fecha,
        [Validators.required]
      ],
      testNumber: [
        this.data.session!.testNumber,
        [Validators.required]
      ],
      categoria_id: [
        this.data.nota?.categoria_id ?? this.data.session!.categoria_id,
        [Validators.required]
      ],
      nif: [
        this.data.nota?.nif ?? '',
        [Validators.required, Validators.minLength(9), Validators.maxLength(10)]
      ],
      nota: [
        this.data.nota?.nota ?? 0,
        [Validators.required, Validators.min(0)]
      ]
    });
  }

  onSubmit(): void {
    // No hacemos nada en bulk tab al pulsar Crear
    if (this.isBulk) {
      return;
    }

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const v = this.form.value;

    if (this.isEdit) {
      // EDITAR nota existente: PUT /api/tests/:id { nota }
      this.testSvc
        .update(this.data.nota!.id!, { nota: v.nota })
        .subscribe(() => this.dialogRef.close(true));

    } else {
      // CREAR nota nueva en sesión existente: POST /api/tests { sessionId, nif, nota, categoria_id }
      this.testSvc
        .create({
          sessionId:    this.data.session!.id,
          nif:          v.nif,
          nota:         v.nota,
          categoria_id: v.categoria_id
        })
        .subscribe(() => this.dialogRef.close(true));
    }
  }

  onFileSelected(evt: Event): void {
    const inp = evt.target as HTMLInputElement;
    if (inp.files?.length) {
      this.selectedFile = inp.files[0];
    }
  }

  onUpload(): void {
    if (!this.selectedFile) {
      return;
    }
    const form = new FormData();
    form.append('file', this.selectedFile);
    this.testSvc.bulkUpload(form)
      .subscribe(() => this.dialogRef.close(true));
  }

  onCancel(): void {
    this.dialogRef.close(false);
  }
}
