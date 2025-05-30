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

import { AsistenciaService } from '../../../services/asistencia.service';
import { CategoriaService, Categoria } from '../../../services/categoria.service';

@Component({
  selector: 'app-asistencia-modal',
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
  templateUrl: './asistencia-modal.component.html',
  styleUrls: ['./asistencia-modal.component.css']
})
export class AsistenciaModalComponent implements OnInit {
  form!: FormGroup;
  categorias: Categoria[] = [];
  isEdit    = false;
  isBulk    = false;
  selectedFile: File | null = null;

  constructor(
    private fb: FormBuilder,
    private asiSvc: AsistenciaService,
    private catSvc: CategoriaService,
    private dialogRef: MatDialogRef<AsistenciaModalComponent>,
    @Inject(MAT_DIALOG_DATA)
    public data: {
      session: { id: number; fecha: string; tipo: string; categoriaId: number } | null;
      asistencia?: {
        id: number;
        categoria_id: number;
        nif: string;
        asiste: boolean;
      };
    }
  ) {
    this.isEdit = !!data.asistencia;
    this.isBulk = (data.session === null);
  }

  ngOnInit() {
    // carga opciones de categoría
    this.catSvc.getAll().subscribe(cs => this.categorias = cs);

    // si es bulk-only, no inicializamos el form individual
    if (this.isBulk) {
      return;
    }

    // inicializamos form de “Individual”
    this.form = this.fb.group({
      fecha: [
        this.data.session!.fecha,
        [Validators.required]
      ],
      tipo: [
        this.data.session!.tipo,
        [Validators.required]
      ],
      categoria_id: [
        this.data.asistencia?.categoria_id ?? this.data.session!.categoriaId,
        [Validators.required]
      ],
      nif: [
        this.data.asistencia?.nif ?? '',
        [Validators.required, Validators.minLength(9), Validators.maxLength(10)]
      ],
      asiste: [
        this.data.asistencia?.asiste ?? false,
        [Validators.required]
      ]
    });
  }

  onSubmit() {
    if (this.isBulk) return;
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    const payload = this.form.value;
    const call = this.isEdit
      ? this.asiSvc.update(this.data.asistencia!.id, payload)
      : this.asiSvc.create({ ...payload, sessionId: this.data.session!.id });
    call.subscribe(() => this.dialogRef.close(true));
  }

  onFileSelected(evt: Event) {
    const inp = evt.target as HTMLInputElement;
    if (inp.files?.length) {
      this.selectedFile = inp.files[0];
    }
  }

  onUpload() {
    if (!this.selectedFile) return;
    const form = new FormData();
    form.append('file', this.selectedFile);
    this.asiSvc.bulkUpload(form).subscribe(() => this.dialogRef.close(true));
  }

  onCancel() {
    this.dialogRef.close(false);
  }
}
