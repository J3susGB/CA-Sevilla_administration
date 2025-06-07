import { Component, inject, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../services/auth.service';
import { ToastService, Toast } from '../shared/services/toast.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  loginError: string | null = null;
  loginForm: FormGroup;
  toasts: Toast[] = [];

  private auth = inject(AuthService);
  private router = inject(Router);
  private fb = inject(FormBuilder);
  private http = inject(HttpClient);
  private toastService = inject(ToastService);

  constructor() {
    this.loginForm = this.fb.group({
      username: ['', [Validators.required]],
      password: ['', [Validators.required, Validators.minLength(6)]],
    });
  }

  ngOnInit(): void {
    this.toastService.toasts$.subscribe((toasts: Toast[]) => {
      this.toasts = toasts;
    });
  }

  dismissToast(id: number) {
    this.toastService.removeToast(id);
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

    sendResetEmail(): void {
    const email = this.loginForm.get('username')?.value;

    if (!email) {
      this.toastService.show('Introduce un email', 'error');
      return;
    }

    this.http.post('http://localhost:8000/api/reset-request', { email }).subscribe({
      next: () => {
        this.toastService.show('Revisa tu correo para restablecer tu contraseña ✉️', 'info');
      },
      error: (err) => {
        const message = err.error?.message || 'Error al enviar el correo de recuperación';
        this.toastService.show(message, 'error');
      }
    });
  }

}
