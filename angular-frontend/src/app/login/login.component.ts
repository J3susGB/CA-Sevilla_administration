import { Component, inject } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  loginError: string | null = null;
  loginForm: FormGroup;

  private auth = inject(AuthService);
  private router = inject(Router);
  private fb = inject(FormBuilder);

  constructor() {
    this.loginForm = this.fb.group({
      username: ['', [Validators.required]],
      password: ['', [Validators.required, Validators.minLength(6)]],
    });
  }

  onSubmit() {
    if (this.loginForm.invalid) {
      return;
    }
    this.loginError = null;

    const { username, password } = this.loginForm.value;
    this.auth.login({ username, password }).subscribe({
      next: () => {
        const roles = this.auth.getRoles();
        // Redirige según rol
        if (roles.includes('ROLE_ADMIN')) {
          this.router.navigate(['/admin']);
        } else if (roles.includes('ROLE_CAPACITACION')) {
          this.router.navigate(['/capacitacion']);
        } else if (roles.includes('ROLE_CLASIFICACION')) {
          this.router.navigate(['/clasificacion']);
        } else if (roles.includes('ROLE_INFORMACION')) {
          this.router.navigate(['/informacion']);
        } else {
          this.router.navigate(['/unauthorized']);
        }
      },
      error: () => {
        this.loginError = 'Credenciales inválidas';
      }
    });
  }
}
