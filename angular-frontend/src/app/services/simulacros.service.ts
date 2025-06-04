// src/app/services/simulacros.service.ts

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Simulacro {
  id: number;
  arbitro_id: number;
  nif: string;
  name: string;
  first_surname: string;
  second_surname?: string;
  categoria_id: number;
  categoria: string;
  fecha: string;     // formato 'd-m-Y'
  periodo: number;   // puede ser decimal
}

@Injectable({ providedIn: 'root' })
export class SimulacrosService {
  private readonly API_URL = 'http://localhost:8000/api/simulacros';

  constructor(private http: HttpClient) {}

  /** Obtener todos los simulacros */
  getAll(): Observable<{ status: string; data: Simulacro[] }> {
    return this.http.get<{ status: string; data: Simulacro[] }>(this.API_URL);
  }

  /** Obtener uno por ID */
  get(id: number): Observable<{ status: string; data: Simulacro }> {
    return this.http.get<{ status: string; data: Simulacro }>(`${this.API_URL}/${id}`);
  }

  /** Crear uno nuevo */
  create(sim: Partial<Simulacro>): Observable<any> {
    return this.http.post(this.API_URL, sim);
  }

  /** Actualizar uno existente */
  update(id: number, sim: Partial<Simulacro>): Observable<any> {
    return this.http.put(`${this.API_URL}/${id}`, sim);
  }

  /** Eliminar uno existente */
  delete(id: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/${id}`);
  }

  /** Carga masiva desde Excel */
  bulkUpload(form: FormData): Observable<any> {
    return this.http.post(`${this.API_URL}/bulk-upload`, form);
  }

}
