// src/app/services/categoria.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';

export interface Categoria {
  id: number;
  nombre: string;
}

@Injectable({ providedIn: 'root' })
export class CategoriaService {
  private readonly API_URL = 'http://localhost:8000/api/categorias';

  /** Listado de categorías (unwrapped) */
  getAll(): Observable<Categoria[]> {
    return this.http
      .get<{ data: Categoria[]; meta: any }>(this.API_URL)
      .pipe(
        map(response => response.data)
      );
  }

  constructor(private http: HttpClient) {}
}
