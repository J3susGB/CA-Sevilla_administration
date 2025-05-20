import { Component, OnInit }                          from '@angular/core';
import { CommonModule }                               from '@angular/common';
import {
  ReactiveFormsModule,
  FormBuilder,
  FormGroup,
  Validators
} from '@angular/forms';

import {
  MatDialogModule,
  MatDialogRef
} from '@angular/material/dialog';
import { MatTabsModule }                              from '@angular/material/tabs';
import { MatFormFieldModule }                         from '@angular/material/form-field';
import { MatInputModule }                             from '@angular/material/input';
import { MatSelectModule }                            from '@angular/material/select';
import { MatButtonModule }                            from '@angular/material/button';
import { Overlay, OverlayModule } from '@angular/cdk/overlay';

import { UserService }                                from '../../../services/user.service';

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
    OverlayModule,
    MatButtonModule
  ],
  templateUrl: './user-modal.component.html',
  styleUrls: ['./user-modal.component.css']
})
export class UserModalComponent implements OnInit {
  userForm!: FormGroup;
  allRoles = ['ROLE_ADMIN', 'ROLE_CAPACITACION', 'ROLE_INFORMACION', 'ROLE_CLASIFICACION'];
  selectedFile: File | null = null;

  constructor(
    private fb: FormBuilder,
    private userSvc: UserService,
    private dialogRef: MatDialogRef<UserModalComponent>,
    public overlay: Overlay 
  ) {}

  ngOnInit() {
    this.userForm = this.fb.group({
      username: ['', Validators.required],
      email:    ['', [Validators.required, Validators.email]],
      roles:    [[], Validators.required]
    });
  }

  onCreate() {
    if (this.userForm.invalid) return;
    this.userSvc.create(this.userForm.value).subscribe({
      next: () => this.dialogRef.close(true),
      error: (err: any) => console.error(err)
    });
  }

  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files && input.files.length
      ? input.files[0]
      : null;
  }

  onUpload() {
    if (!this.selectedFile) return;
    const form = new FormData();
    form.append('file', this.selectedFile);
    this.userSvc.bulkUpload(form).subscribe({
      next: () => this.dialogRef.close(true),
      error: (err: any) => console.error(err)
    });
  }
}
