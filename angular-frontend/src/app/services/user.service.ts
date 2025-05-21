// src/app/services/user.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { API_URL } from '../app.config';

// Define aquí la interfaz que devuelve tu API
export interface User {
  id: number;
  username: string;
  email: string;
  roles: string[];
}

@Injectable({ providedIn: 'root' })
export class UserService {
  private readonly API_URL = `${API_URL}/users`;

  constructor(private http: HttpClient) {}

  /** Obtiene la lista completa de usuarios */
  getAll(): Observable<User[]> {
    return this.http.get<User[]>(this.API_URL);
  }

  /** Obtener uno */
  getById(id: number): Observable<User> {
    return this.http.get<User>(`${this.API_URL}/${id}`);
  }

  /** Crea un usuario */
  create(user: Partial<User>): Observable<User> {
    return this.http.post<User>(this.API_URL, user);
  }

  /** Actualizar */
  update(id: number, u: Partial<User>): Observable<any> {
    return this.http.put(`${this.API_URL}/${id}`, u);
  }

  /** Borrar */
  delete(id: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/${id}`);
  }

  checkUsername(username: string): Observable<{ exists: boolean }> {
    return this.http.post<{ exists: boolean }>(
      `${this.API_URL}/check-username`,
      { username }
    );
  }

  checkEmail(email: string): Observable<{ exists: boolean }> {
    return this.http.post<{ exists: boolean }>(
      `${this.API_URL}/check-email`,
      { email }
    );
  }

  /** Bulk upload */
  bulkUpload(form: FormData): Observable<any> {
    return this.http.post(`${this.API_URL}/bulk-upload`, form);
  }
}
