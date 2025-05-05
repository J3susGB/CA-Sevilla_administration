// angular-frontend/src/app/services/auth.service.ts

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap }        from 'rxjs/operators';
import { Observable } from 'rxjs';

interface LoginDTO      { username: string; password: string; }
interface LoginResponse { token: string;            }

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly API_URL = 'http://localhost:8000/api/login';

  constructor(private http: HttpClient) {}

  login(credentials: LoginDTO): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(this.API_URL, credentials).pipe(
      tap(response => {
        localStorage.setItem('token', response.token);
      })
    );
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  logout(): void {
    localStorage.removeItem('token');
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }
}
