// src/app/dashboard-admin/users/user-modal/user-modal.component.ts

import { Component, Inject, OnInit } from '@angular/core';
import { CommonModule }               from '@angular/common';
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

import { UserService, User } from '../../../services/user.service';

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
  allRoles = ['ROLE_ADMIN','ROLE_CAPACITACION','ROLE_INFORMACION','ROLE_CLASIFICACION'];
  selectedFile: File | null = null;
  isEditMode = false;

  constructor(
    private fb: FormBuilder,
    private userSvc: UserService,
    private dialogRef: MatDialogRef<UserModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { user?: User }
  ) {}

  ngOnInit() {
    this.isEditMode = !!this.data?.user;
    this.userForm = this.fb.group({
      username: [this.data?.user?.username || '', Validators.required],
      email:    [this.data?.user?.email    || '', [Validators.required, Validators.email]],
      roles:    [this.data?.user?.roles    || [], Validators.required],
      password: ['', this.isEditMode ? [] : Validators.required]
    });
  }

  onCreate() {
    if (this.userForm.invalid) return;
    const fv = this.userForm.value;

    if (this.isEditMode) {
      const payload: Partial<User> = {
        username: fv.username,
        email:    fv.email,
        roles:    fv.roles
      };
      this.userSvc.update(this.data.user!.id!, payload)
        .subscribe(() => this.dialogRef.close(true));
    } else {
      const payload = {
        username: fv.username,
        email:    fv.email,
        roles:    fv.roles,
        password: fv.password
      };
      this.userSvc.create(payload)
        .subscribe(() => this.dialogRef.close(true));
    }
  }

  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files?.length ? input.files[0] : null;
  }

  onUpload() {
    console.log('🛠️ onUpload() disparado — selectedFile=', this.selectedFile);
    if (!this.selectedFile) return;
    const form = new FormData();
    form.append('file', this.selectedFile);
    this.userSvc.bulkUpload(form)
      .subscribe(() => this.dialogRef.close(true));
  }
}
