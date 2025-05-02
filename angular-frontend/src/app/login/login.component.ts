import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, RouterModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  loginForm: FormGroup;

  constructor(private fb: FormBuilder) {
    //Se crea formulario reactivo con validaciones
    this.loginForm = this.fb.group({
      username: ['', [Validators.required]], // username requerido
      password: ['', [Validators.required, Validators.minLength(6)]] //Password requerido y mínimo 6 caracteres
    });
  }

  //Función que se llama al enviar el formulario
  onSubmit() {
    if (this.loginForm.invalid) {
      console.log('Formulario inválido');
      return;
    }

    //Aquí iría la llamada al backend
    console.log('Datos del formulrio:', this.loginForm.value);
  }
}
