// src/app/services/entrenamientos.service.ts

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Entrenamiento {
  id: number;
  arbitro_id: number;
  nif: string;
  name: string;
  first_surname: string;
  second_surname?: string;
  categoria_id: number;
  categoria: string;
  septiembre: number;
  octubre: number;
  noviembre: number;
  diciembre: number;
  enero: number;
  febrero: number;
  marzo: number;
  abril: number;
}

@Injectable({ providedIn: 'root' })
export class EntrenamientosService {
  private readonly API_URL = 'http://localhost:8000/api/entrenamientos';

  constructor(private http: HttpClient) {}

  /** Obtener todos los entrenamientos */
  getAll(): Observable<{ status: string; data: Entrenamiento[] }> {
    return this.http.get<{ status: string; data: Entrenamiento[] }>(this.API_URL);
  }

  /** Obtener uno por ID */
  get(id: number): Observable<{ status: string; data: Entrenamiento }> {
    return this.http.get<{ status: string; data: Entrenamiento }>(`${this.API_URL}/${id}`);
  }

  /** Crear uno nuevo */
  create(ent: Partial<Entrenamiento>): Observable<any> {
    return this.http.post(this.API_URL, ent);
  }

  /** Actualizar uno existente */
  update(id: number, ent: Partial<Entrenamiento>): Observable<any> {
    return this.http.put(`${this.API_URL}/${id}`, ent);
  }

  /** Eliminar */
  delete(id: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/${id}`);
  }

  /** Carga masiva */
  bulkUpload(form: FormData): Observable<any> {
    return this.http.post(`${this.API_URL}/bulk-upload`, form);
  }

  /** Truncar tabla */
  truncate(): Observable<any> {
    return this.http.post(`${this.API_URL}/truncate`, {});
  }

  /** Totales por árbitro */
  getTotales(): Observable<{ status: string; data: any[] }> {
    return this.http.get<{ status: string; data: any[] }>(`${this.API_URL}/total-por-arbitro`);
  }
}
