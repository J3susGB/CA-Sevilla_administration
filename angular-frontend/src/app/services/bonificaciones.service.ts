// src/app/services/bonificaciones.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Bonificacion {
  id: number;
  name: string;
  valor: string;
  categoria_id: number;
  categoria_name?: string;  // opcional para mostrar el nombre de la categoría
}

@Injectable({ providedIn: 'root' })
export class BonificacionesService {
  private readonly API_URL = 'http://localhost:8000/api/bonificaciones';

  constructor(private http: HttpClient) {}

  /** Listado paginado de bonificaciones */
  getAll(page = 1, limit = 25): Observable<{ status: string; data: Bonificacion[]; meta: any }> {
    return this.http.get<any>(`${this.API_URL}?page=${page}&limit=${limit}`);
  }

  /** Obtener una bonificación por ID */
  get(id: number): Observable<{ status: string; data: Bonificacion }> {
    return this.http.get<any>(`${this.API_URL}/${id}`);
  }

  /** Crear nueva bonificación */
  create(bonificacion: Partial<Bonificacion>): Observable<any> {
    return this.http.post<any>(this.API_URL, bonificacion);
  }

  /** Actualizar bonificación existente */
  update(id: number, bonificacion: Partial<Bonificacion>): Observable<any> {
    return this.http.put<any>(`${this.API_URL}/${id}`, bonificacion);
  }

  /** Eliminar bonificación */
  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.API_URL}/${id}`);
  }

  /** Carga masiva de bonificaciones */
  bulkUpload(form: FormData): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/bulk-upload`, form);
  }
}
