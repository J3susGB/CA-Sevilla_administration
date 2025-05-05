import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs/operators';
import { Observable } from 'rxjs';

interface LoginDTO { username: string; password: string; }
interface LoginResponse { token: string; }

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly API_URL   = 'http://localhost:8000/api/login';
  private readonly TOKEN_KEY = 'auth_token';
  private readonly USER_KEY  = 'username';

  constructor(private http: HttpClient) { }

  /** Borra token y username del localStorage */
  clearSession(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
  }

  /** Hace login, guarda token + username en localStorage */
  login(credentials: LoginDTO): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(this.API_URL, credentials).pipe(
      tap(response => {
        // 1) Guarda token
        localStorage.setItem(this.TOKEN_KEY, response.token);

        // 2) Decodifica payload (Base64-URL → Base64) y extrae username
        const base64Url = response.token.split('.')[1];
        const base64 = base64Url
          .replace(/-/g, '+')
          .replace(/_/g, '/')
          + '='.repeat((4 - (base64Url.length % 4)) % 4);
        const payload = JSON.parse(window.atob(base64));
        localStorage.setItem(this.USER_KEY, payload.username);
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

  /** Logout: limpia session y username */
  logout(): void {
    this.clearSession();
  }

  /** True si el usuario está logueado */
  isAuthenticated(): boolean {
    return !!this.getToken();
  }
}
