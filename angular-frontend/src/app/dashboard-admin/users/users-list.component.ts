import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { UserService, User } from '../../services/user.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-users-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './users-list.component.html'
})
export class UsersListComponent implements OnInit {
  users: User[] = [];

  constructor(
    private userService: UserService,
    private auth: AuthService
  ) {}

  ngOnInit(): void {
    // Solo administrador
    if (!this.auth.getRoles().includes('ROLE_ADMIN')) {
      return;
    }

    // Carga del listado
    this.userService.getAll().subscribe((list: User[]) => {
      this.users = list;
    });
  }
}
