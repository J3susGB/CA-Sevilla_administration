// src/app/services/user.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

// Define aquí la interfaz que devuelve tu API
export interface User {
  id: number;
  username: string;
  email: string;
  roles: string[];
}

@Injectable({ providedIn: 'root' })
export class UserService {
  private readonly API_URL = 'http://localhost:8000/api/users';

  constructor(private http: HttpClient) {}

  /** Obtiene la lista completa de usuarios */
  getAll(): Observable<User[]> {
    return this.http.get<User[]>(this.API_URL);
  }

  /** Crea un usuario */
  create(user: Partial<User>): Observable<User> {
    return this.http.post<User>(this.API_URL, user);
  }

  /** Subida masiva vía Excel */
  bulkUpload(formData: FormData): Observable<any> {
    return this.http.post(`${this.API_URL}/bulk-upload`, formData);
  }
}
