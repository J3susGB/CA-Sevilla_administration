import { Injectable } from "@angular/core";
import { CanActivate, Router } from "@angular/router";

// Importación del servicio de autenticación
import { AuthService } from "../services/auth.service";

@Injectable({
    providedIn: 'root'
})
export class AuthGuard implements CanActivate {

    // Se inyecta el AuthService y el Router para comprobar estado y redirigir si hace falta
    constructor(
        private authService: AuthService,
        private router: Router
    ) {}

    // Función para controlar si puede entrar en ruta protegida
    canActivate(): boolean {
        //Si el usuario está autenticado (si tiene un token en localStorage)
        if (this.authService.isAuthenticated()) {
            return true; // Le damos paso
        }

        // Si no está autenticado, redirección a login
        this.router.navigate(['/']);
        return false;
    }
}