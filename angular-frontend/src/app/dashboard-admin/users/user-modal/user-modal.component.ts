// src/app/dashboard-admin/users/user-modal/user-modal.component.ts

import { Component, OnInit, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  ReactiveFormsModule,
  FormBuilder,
  FormGroup,
  Validators,
  AsyncValidatorFn,
  AbstractControl,
  ValidationErrors
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

import { UserService, User } from '../../../services/user.service';
import { debounceTime, switchMap, map, first } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
  selector: 'app-user-modal',
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
  templateUrl: './user-modal.component.html',
  styleUrls: ['./user-modal.component.css']
})
export class UserModalComponent implements OnInit {
  userForm!: FormGroup;
  allRoles = [
    'ROLE_ADMIN',
    'ROLE_CAPACITACION',
    'ROLE_INFORMACION',
    'ROLE_CLASIFICACION'
  ];
  selectedFile: File | null = null;
  isEditMode = false;

  constructor(
    private fb: FormBuilder,
    private userSvc: UserService,
    private dialogRef: MatDialogRef<UserModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { user?: User }
  ) {}

  ngOnInit(): void {
    // Detectar modo edición si recibimos un usuario
    this.isEditMode = !!this.data?.user;

    // Inicializar el formulario con validaciones
    this.userForm = this.fb.group({
      username: [
        this.data?.user?.username || '',
        Validators.required,
        this.usernameUniqueValidator()
      ],
      email: [
        this.data?.user?.email || '',
        [Validators.required, Validators.email],
        this.emailUniqueValidator()
      ],
      roles: [
        this.data?.user?.roles || [],
        Validators.required
      ],
      // La contraseña solo es obligatoria en crear y con mínimo 6 caracteres
      password: [
        '',
        this.isEditMode
          ? []
          : [Validators.required, Validators.minLength(6)]
      ]
    });
  }

  // Async validator para comprobar que el username no exista
  private usernameUniqueValidator(): AsyncValidatorFn {
    return (control: AbstractControl) => {
      // Si en edición no cambia el usuario, no validar
      if (this.isEditMode && control.value === this.data.user?.username) {
        return of(null);
      }
      if (!control.value) {
        return of(null);
      }
      return of(control.value).pipe(
        debounceTime(300),
        switchMap(username =>
          this.userSvc.checkUsername(username)
        ),
        map(res => res.exists ? { usernameTaken: true } : null),
        first()
      );
    };
  }

  // Async validator para comprobar que el email no exista
  private emailUniqueValidator(): AsyncValidatorFn {
    return (control: AbstractControl) => {
      // Si en edición no cambia el email, no validar
      if (this.isEditMode && control.value === this.data.user?.email) {
        return of(null);
      }
      if (!control.value) {
        return of(null);
      }
      return of(control.value).pipe(
        debounceTime(300),
        switchMap(email =>
          this.userSvc.checkEmail(email)
        ),
        map(res => res.exists ? { emailTaken: true } : null),
        first()
      );
    };
  }

  onCreate(): void {
    // Si hay errores, marcamos para mostrar mensajes
    if (this.userForm.invalid) {
      this.userForm.markAllAsTouched();
      return;
    }

    const formValue = this.userForm.value;

    if (this.isEditMode) {
      // Modo edición: no enviar password vacío
      const payload: Partial<User & { password?: string }> = {
        username: formValue.username,
        email:    formValue.email,
        roles:    formValue.roles
      };
      if (formValue.password) {
        payload.password = formValue.password;
      }

      this.userSvc
        .update(this.data.user!.id!, payload)
        .subscribe({
          next: () => this.dialogRef.close(true),
          error: err => console.error(err)
        });
    } else {
      // Modo creación
      this.userSvc
        .create(formValue)
        .subscribe({
          next: () => this.dialogRef.close(true),
          error: err => console.error(err)
        });
    }
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files?.[0] ?? null;
  }

  onUpload(): void {
    if (!this.selectedFile) {
      return;
    }
    const form = new FormData();
    form.append('file', this.selectedFile);
    this.userSvc.bulkUpload(form).subscribe({
      next: () => this.dialogRef.close(true),
      error: err => console.error(err)
    });
  }
}
