// src/app/dashboard-admin/users/users-list.component.ts

import { Component, OnInit } from '@angular/core';
import { CommonModule }      from '@angular/common';
import { RouterModule }      from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';

import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatButtonModule }            from '@angular/material/button';
import { MatIconModule }              from '@angular/material/icon';
import { MatFormFieldModule }         from '@angular/material/form-field';
import { MatInputModule }             from '@angular/material/input';

import { UserService, User } from '../../services/user.service';
import { AuthService }       from '../../services/auth.service';
import { UserModalComponent } from './user-modal/user-modal.component';

@Component({
  selector: 'app-users-list',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule
  ],
  templateUrl: './users-list.component.html',
  styleUrls: ['./users-list.component.css']
})
export class UsersListComponent implements OnInit {
  users: User[]         = [];
  filteredUsers: User[] = [];
  filterForm!: FormGroup;

  constructor(
    private userService: UserService,
    private auth: AuthService,
    private dialog: MatDialog,
    private fb: FormBuilder
  ) {}

  ngOnInit(): void {
    if (!this.auth.getRoles().includes('ROLE_ADMIN')) {
      return;
    }

    this.filterForm = this.fb.group({
      usernameFilter: [''],
      roleFilter:     ['']
    });

    // debug: comprobar que subcribe
    this.filterForm.valueChanges.subscribe(vals => {
      console.log('◼️ valueChanges:', vals);
      this.applyFilter();
    });

    this.load();
  }

  load(): void {
    this.userService.getAll().subscribe(list => {
      this.users = list;
      this.filteredUsers = [...list];
      this.applyFilter();
    });
  }

  applyFilter(): void {
    const { usernameFilter, roleFilter } = this.filterForm.value;
    console.log('🔍 Filters changed:', { usernameFilter, roleFilter });

    const uf = usernameFilter.trim().toLowerCase();
    const rf = roleFilter.trim().toLowerCase();

    this.filteredUsers = this.users.filter(u => {
      const matchesUser = !uf
        || u.username.toLowerCase().includes(uf);
      const matchesRole = !rf
        || u.roles.some(r => r.toLowerCase().includes(rf));
      return matchesUser && matchesRole;
    });
  }

  addUser(): void {
    this.dialog.open(UserModalComponent, { width: '500px', panelClass: 'user-modal-dialog' })
      .afterClosed().subscribe(created => {
        if (created) this.load();
      });
  }

  editUser(u: User): void {
    this.dialog.open(UserModalComponent, {
      width: '500px',
      data: { user: u },
      panelClass: 'user-modal-dialog'
    }).afterClosed().subscribe(done => done && this.load());
  }

  deleteUser(u: User): void {
    if (!confirm(`¿Eliminar usuario ${u.username}?`)) return;
    this.userService.delete(u.id!).subscribe(() => this.load());
  }
}
