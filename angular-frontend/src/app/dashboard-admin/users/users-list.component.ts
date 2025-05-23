// src/app/dashboard-admin/users/users-list.component.ts

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
import { UserModalComponent }        from './user-modal/user-modal.component';

import { ToastService, Toast }       from '../../shared/services/toast.service';

@Component({
  selector: 'app-users-list',
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
  templateUrl: './users-list.component.html',
  styleUrls: ['./users-list.component.css']
})
export class UsersListComponent implements OnInit {
  users: User[] = [];
  filteredUsers: User[] = [];
  filterForm!: FormGroup;
  toasts: Toast[] = [];

  constructor(
    private userService: UserService,
    private auth: AuthService,
    private dialog: MatDialog,
    private fb: FormBuilder,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    if (!this.auth.getRoles().includes('ROLE_ADMIN')) {
      return;
    }

    this.toastService.toasts$.subscribe((toasts: Toast[]) => {
      this.toasts = toasts;
    });

    this.filterForm = this.fb.group({
      usernameFilter: [''],
      roleFilter: ['']
    });
    this.filterForm.valueChanges.subscribe(vals => {
      console.log('◼️ valueChanges:', vals);
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

  addUser(): void {
    this.dialog
      .open(UserModalComponent, {
        width: '500px',
        panelClass: 'user-modal-dialog'
      })
      .afterClosed()
      .subscribe(created => {
        if (created) {
          this.load();
          this.toastService.show('Añadido con éxito ✅', 'success');
        }
      });
  }

  editUser(u: User): void {
    this.dialog
      .open(UserModalComponent, {
        width: '500px',
        data: { user: u },
        panelClass: 'user-modal-dialog'
      })
      .afterClosed()
      .subscribe(done => {
        if (done) {
          this.load();
          this.toastService.show('Actualizado con éxito ✅', 'success');
        }
      });
  }

  deleteUser(u: User): void {
    // 1) Datos para el diálogo
    const data: ConfirmDialogData = {
      title: `Eliminar a ${u.username}?`,
      message: '¡Esta acción no se puede deshacer!',
      confirmText: 'Eliminar',
      cancelText: 'Cancelar'
    };

    // 2) Abrimos confirm dialog
    this.dialog
      .open(ConfirmDialogComponent, { 
        data,
        panelClass: 'confirm-dialog-panel',
        backdropClass: 'confirm-dialog-backdrop'
       })
      .afterClosed()
      .subscribe(confirmed => {
        if (!confirmed) {
          return; // usuario canceló
        }
        // 3) Borramos y mostramos toast
        this.userService.delete(u.id!).subscribe({
          next: () => {
            this.load();
            this.toastService.show('Usuario eliminado con éxito 🗑️', 'error');
          },
          error: err => {
            console.error(err);
            this.toastService.show('Error al eliminar usuario ❌', 'error');
          }
        });
      });
  }
}
