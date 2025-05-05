// angular-frontend/src/app/login/login.component.ts

import { Component }                from '@angular/core';
import { Router }                   from '@angular/router';
import { FormBuilder, FormGroup, Validators }  from '@angular/forms';
import { CommonModule }             from '@angular/common';
import { ReactiveFormsModule }      from '@angular/forms';
import { AuthService }              from '../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  loginError: string | null = null;

  loginForm!: FormGroup;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    
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
    this.authService.login({ username, password }).subscribe({
      next: () => {
        this.router.navigate(['dashboard']);
      },
      error: () => {
        this.loginError = 'Credenciales inválidas';
      }
    });
  }
}
