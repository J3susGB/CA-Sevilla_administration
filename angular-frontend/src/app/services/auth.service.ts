// src/app/services/auth.service.ts

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs/operators';
import { Observable } from 'rxjs';

interface LoginDTO   { username: string; password: string; }
interface LoginResponse { token: string; }
interface JwtPayload { username: string; roles: string[]; }

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly API_URL    = 'http://localhost:8000/api/login';
  private readonly TOKEN_KEY  = 'auth_token';
  private readonly USER_KEY   = 'username';
  private readonly ROLES_KEY  = 'user_roles';

  constructor(private http: HttpClient) {}

  /** Borra token, username y roles del localStorage */
  clearSession(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
    localStorage.removeItem(this.ROLES_KEY);
  }

  /** Hace login, guarda token + username + roles en localStorage */
  login(credentials: LoginDTO): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(this.API_URL, credentials).pipe(
      tap(response => {
        // 1) Guarda el token
        localStorage.setItem(this.TOKEN_KEY, response.token);

        // 2) Decodifica el payload del JWT manualmente
        const base64Url = response.token.split('.')[1];
        const base64 = base64Url
          .replace(/-/g, '+')
          .replace(/_/g, '/')
          + '='.repeat((4 - (base64Url.length % 4)) % 4);
        const jsonPayload = window.atob(base64);
        const payload = JSON.parse(jsonPayload) as JwtPayload;

        // 3) Guarda username y roles en localStorage
        localStorage.setItem(this.USER_KEY, payload.username);
        localStorage.setItem(this.ROLES_KEY, JSON.stringify(payload.roles));
      })
    );
  }

  /** Devuelve el token JWT */
  getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  /** Devuelve el username guardado */
  getUsername(): string | null {
    return localStorage.getItem(this.USER_KEY);
  }

  /** Devuelve el array de roles */
  getRoles(): string[] {
    const roles = localStorage.getItem(this.ROLES_KEY);
    return roles ? JSON.parse(roles) : [];
  }

  /** Logout: limpia sesión */
  logout(): void {
    this.clearSession();
  }

  /** True si el usuario está autenticado */
  isAuthenticated(): boolean {
    return !!this.getToken();
  }
}
