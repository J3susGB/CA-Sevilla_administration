// src/app/dashboard-admin/users/users-list.component.ts

import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

import { UserService, User } from '../../services/user.service';
import { AuthService } from '../../services/auth.service';
import { UserModalComponent } from './user-modal/user-modal.component';

@Component({
  selector: 'app-users-list',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    MatDialogModule,
    MatButtonModule,
    MatIconModule
  ],
  templateUrl: './users-list.component.html',
  styleUrls: ['./users-list.component.css']
})
export class UsersListComponent implements OnInit {
  users: User[] = [];

  constructor(
    private userService: UserService,
    private auth: AuthService,
    private dialog: MatDialog
  ) { }

  ngOnInit(): void {
    if (!this.auth.getRoles().includes('ROLE_ADMIN')) {
      return;
    }
    this.load();
  }

  load(): void {
    this.userService.getAll().subscribe(list => this.users = list);
  }

  addUser(): void {
  this.dialog.open(UserModalComponent, {
    width: '500px',
    panelClass: 'user-modal-dialog'    // <<< aquí, STRING
  }).afterClosed().subscribe(created => {
    if (created) this.load();
  });
}

}
