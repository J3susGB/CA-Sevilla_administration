// src/app/dashboard-admin/tecnicos/tecnicos-modal/tecnicos-modal.component.ts

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
import { MatCheckboxModule }  from '@angular/material/checkbox';
import { MatButtonModule }    from '@angular/material/button';
import { MatTabsModule }      from '@angular/material/tabs';

import { TecnicoService } from '../../../services/tecnico.service';
import { CategoriaService, Categoria } from '../../../services/categoria.service';

@Component({
  selector: 'app-tecnicos-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatCheckboxModule,
    MatButtonModule,
    MatTabsModule
  ],
  templateUrl: './tecnicos-modal.component.html',
  styleUrls:   ['./tecnicos-modal.component.css']
})
export class TecnicosModalComponent implements OnInit {
  form!: FormGroup;
  categories: Categoria[] = [];

  /** Si data.nota existe, originalmente veníamos a editar, pero ahora solo usamos create */
  isEdit = false;
  /** Si data.session === null, abrimos directamente el tab “Carga masiva” */
  isBulk = false;

  selectedFile: File | null = null;

  constructor(
    private fb: FormBuilder,
    private tecSvc: TecnicoService,
    private catSvc: CategoriaService,
    private dialogRef: MatDialogRef<TecnicosModalComponent>,
    @Inject(MAT_DIALOG_DATA)
    public data: {
      session: {
        id: number;
        fecha: string;
        examNumber: number;
        categoria: string;
        categoria_id: number;
      } | null;
      nota?: {
        id?: number;
        arbitro_id: number;
        categoria_id: number;
        nif: string;
        nota: number;
        repesca: boolean;
      };
    }
  ) {
    // Seguimos marcando isEdit para mostrar “Editar” en el título, pero no vamos a llamar a PUT.
    this.isEdit = !!data.nota;
    this.isBulk = data.session === null;
  }

  ngOnInit(): void {
    // Cargar la lista de categorías para el select
    this.catSvc.getAll().subscribe(cs => this.categories = cs);

    // Si estamos en carga masiva, no montamos el formulario “Individual”
    if (this.isBulk) {
      return;
    }

    // Creamos el formulario para “Individual” (crear ó editar)
    this.form = this.fb.group({
      fecha: [
        this.data.session!.fecha,
        [Validators.required]
      ],
      examNumber: [
        this.data.session!.examNumber,
        [Validators.required, Validators.min(1), Validators.max(3)]
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
      ],
      repesca: [
        this.data.nota?.repesca ?? false
      ]
    });
  }

  /**
   * Al pulsar “Crear” / “Guardar cambios” en el tab “Individual”:
   * en ambos casos llamamos a POST /api/tecnicos, dejando que el backend cree o actualice.
   */
  onSubmit(): void {
    if (this.isBulk) {
      return;
    }

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const v = this.form.value;

    // Payload idéntico para crear o editar:
    const payload: {
      sessionId: number;
      nif: string;
      nota: number;
      repesca: boolean;
      categoria_id: number;
    } = {
      sessionId:    this.data.session!.id,
      nif:          v.nif,
      nota:         v.nota,
      repesca:      v.repesca,
      categoria_id: v.categoria_id
    };

    this.tecSvc.create(payload)
      .subscribe(
        () => {
          // Al cerrar, pasamos true para que la lista se recargue
          this.dialogRef.close(true);
        },
        () => {
          // Puedes mostrar un error o simplemente cerrar con false
          this.dialogRef.close(false);
        }
      );
  }

  /**
   * Cuando cambiamos el fichero en “Carga masiva”
   */
  onFileSelected(evt: Event): void {
    const inp = evt.target as HTMLInputElement;
    if (inp.files?.length) {
      this.selectedFile = inp.files[0];
    }
  }

  /**
   * Al pulsar “Subir” en el tab “Carga masiva”
   */
  onUpload(): void {
    if (!this.selectedFile) {
      return;
    }
    const form = new FormData();
    form.append('file', this.selectedFile);
    this.tecSvc.bulkUpload(form)
      .subscribe(
        () => this.dialogRef.close(true),
        () => this.dialogRef.close(false)
      );
  }

  onCancel(): void {
    this.dialogRef.close(false);
  }
}
