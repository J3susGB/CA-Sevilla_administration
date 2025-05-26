// src/app/services/categoria.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';    // ← importa map desde rxjs/operators

export interface Categoria {
  id: number;
  nombre: string;
}

@Injectable({ providedIn: 'root' })
export class CategoriaService {
  private readonly API_URL = 'http://localhost:8000/api/categorias';

  // 1) Inyecta HttpClient
  constructor(private http: HttpClient) {}

  /** Listado de categorías (unwrapped) */
  getAll(): Observable<Categoria[]> {
    return this.http
      .get<{ data: Categoria[]; meta: any }>(this.API_URL)
      .pipe(
        // 2) Ahora map está correctamente importado
        map(response => response.data)
      );
  }

  /** Obtener una categoría */
  get(id: number): Observable<{ status: string; data: Categoria }> {
    return this.http.get<any>(`${this.API_URL}/${id}`);
  }

  /** Crear nueva categoría */
  create(cat: Partial<Categoria>): Observable<any> {
    return this.http.post<any>(this.API_URL, cat);
  }

  /** Actualizar categoría */
  update(id: number, cat: Partial<Categoria>): Observable<any> {
    return this.http.put<any>(`${this.API_URL}/${id}`, cat);
  }

  /** Eliminar categoría */
  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.API_URL}/${id}`);
  }
}
