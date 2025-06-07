import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { ToastService } from '../shared/services/toast.service';

@Component({
  selector: 'app-reset-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.css']
})
export class ResetPasswordComponent implements OnInit {
  resetForm!: FormGroup;
  token: string = '';
  formError: string | null = null;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private http: HttpClient,
    private toast: ToastService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.token = this.route.snapshot.queryParamMap.get('token') || '';

    this.resetForm = this.fb.group({
      newPassword: ['', [Validators.required, Validators.minLength(6)]],
      confirmPassword: ['', Validators.required],
    });
  }

  onSubmit(): void {
    const { newPassword, confirmPassword } = this.resetForm.value;

    if (this.resetForm.invalid) return;

    if (newPassword !== confirmPassword) {
      this.formError = 'Las contraseñas no coinciden';
      return;
    }

    this.http.post('http://localhost:8000/api/reset-password', {
      token: this.token,
      password: newPassword
    }).subscribe({
      next: () => {
        this.toast.show('Contraseña actualizada correctamente 🔐', 'success');
        this.router.navigate(['/login']);
      },
      error: (err) => {
        this.formError = err.error?.message || 'Token inválido o expirado';
      }
    });
  }
}
