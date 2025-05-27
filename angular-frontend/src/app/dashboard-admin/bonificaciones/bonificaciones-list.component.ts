import { Component, OnInit } from '@angular/core';
import { CommonModule }      from '@angular/common';
import { RouterModule }      from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';

// Angular Material
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatButtonModule }            from '@angular/material/button';
import { MatIconModule }              from '@angular/material/icon';
import { MatFormFieldModule }         from '@angular/material/form-field';
import { MatInputModule }             from '@angular/material/input';

// Dálogo de confirmación 
import {
  ConfirmDialogComponent,
  ConfirmDialogData
} from '../../shared/components/confirm-dialog/confirm-dialog.component';

import { UserService, User }         from '../../services/user.service';
import { AuthService }               from '../../services/auth.service';

import { ToastService, Toast }       from '../../shared/services/toast.service';

@Component({
  selector: 'app-bonificaciones-list',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    ReactiveFormsModule,

    // módulos Angular Material
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,

    // añade ConfirmDialogComponent 
    ConfirmDialogComponent
  ],
  templateUrl: './bonificaciones-list.component.html',
  styleUrls: ['./bonificaciones-list.component.css']
})
export class bonificacionesListComponent implements OnInit {
  users: User[] = [];
  filteredUsers: User[] = [];
  filterForm!: FormGroup;
  toasts: Toast[] = [];
  backLink = '/';  // ruta por defecto

  constructor(
    private userService: UserService,
    private auth: AuthService,
    private dialog: MatDialog,
    private fb: FormBuilder,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    // Solo ADMIN y CAPACITACION
    if (!this.auth.getRoles().some(r => ['ROLE_ADMIN', 'ROLE_CAPACITACION'].includes(r))) {
      return;
    }

    // Calcula la ruta “atrás” según rol
    const roles = this.auth.getRoles();
    if (roles.includes('ROLE_ADMIN')) {
      this.backLink = '/admin';
    } else if (roles.includes('ROLE_CAPACITACION')) {
      this.backLink = '/capacitacion';
    }

    this.toastService.toasts$.subscribe((toasts: Toast[]) => {
      this.toasts = toasts;
    });

    this.filterForm = this.fb.group({
      usernameFilter: [''],
      roleFilter: ['']
    });
    this.filterForm.valueChanges.subscribe(vals => {
      this.applyFilter();
    });

    this.load();
  }

  dismissToast(id: number): void {
    this.toastService.removeToast(id);
  }

  private load(): void {
    this.userService.getAll().subscribe(list => {
      this.users = list;
      this.filteredUsers = [...list];
      this.applyFilter();
    });
  }

  private applyFilter(): void {
    const { usernameFilter, roleFilter } = this.filterForm.value;
    const uf = usernameFilter.trim().toLowerCase();
    const rf = roleFilter.trim().toLowerCase();

    this.filteredUsers = this.users.filter(u => {
      const matchesUser = !uf || u.username.toLowerCase().includes(uf);
      const matchesRole = !rf || u.roles.some(r => r.toLowerCase().includes(rf));
      return matchesUser && matchesRole;
    });
  }
}