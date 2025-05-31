// src/app/services/tecnico.service.ts

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

/**
 * Interfaz para una nota técnica de un árbitro dentro de una sesión.
 * Coincide con cada elemento 'notas' que devuelve GET /api/tecnicos.
 */
export interface TecnicoNote {
  id?: number;              // ID en la tabla 'tecnicos' o null si no existe (nota sintética)
  arbitro_id: number;
  nif: string;
  first_surname: string;
  second_surname?: string;
  name: string;
  nota: number;
  repesca: boolean;
}

/**
 * Interfaz para una sesión técnica con todas sus notas.
 * Corresponde a cada objeto que devuelve GET /api/tecnicos.
 */
export interface TecnicoSession {
  id: number;
  fecha: string;        // formato "dd-MM-YYYY"
  examNumber: number;
  categoria: string;    // "Provincial", "Oficial", etc.
  categoria_id: number;
  notas: TecnicoNote[];
}

/**
 * Interfaz para la respuesta de /api/tecnicos/report
 * (agrupado por categoría → examen → árbitros con nota + repesca)
 */
export interface TecnicoReportByCategory {
  categoria_id: number;
  categoria: string;
  exams: {
    [examNumber: number]: Array<{
      first_surname: string;
      second_surname?: string;
      name: string;
      nota: number;
      repesca: boolean;
    }>;
  };
}

@Injectable({ providedIn: 'root' })
export class TecnicoService {
  private readonly API_URL = 'http://localhost:8000/api/tecnicos';

  constructor(private http: HttpClient) {}

  /**
   * Listar todas las sesiones técnicas con sus notas (incluye notas sintéticas de 0).
   * GET /api/tecnicos
   */
  listAll(): Observable<{ status: string; data: TecnicoSession[] }> {
    return this.http.get<any>(this.API_URL);
  }

  /**
   * Obtener una nota individual (opcional, raramente se use directamente).
   * GET /api/tecnicos/{id}
   */
  get(id: number): Observable<{ status: string; data: TecnicoNote }> {
    return this.http.get<any>(`${this.API_URL}/${id}`);
  }

  /**
   * Reporte “por categoría”: GET /api/tecnicos/report
   */
  report(): Observable<{ status: string; data: TecnicoReportByCategory[] }> {
    return this.http.get<any>(`${this.API_URL}/report`);
  }

  /**
   * Crear o actualizar una nota técnica individual.
   * POST /api/tecnicos { nif, nota, categoria_id, sessionId? } ó
   * POST /api/tecnicos { nif, nota, categoria_id, fecha, examNumber }
   */
  create(body: {
    nif: string;
    nota: number;
    categoria_id: number;
    sessionId?: number;
    fecha?: string;
    examNumber?: number;
    repesca?: boolean;
  }): Observable<any> {
    return this.http.post(this.API_URL, body);
  }

  /**
   * Actualizar solo la nota (y repesca) de una nota técnica existente.
   * PUT /api/tecnicos/{id} { nota, repesca? }
   */
  update(id: number, body: any): Observable<any> {
    return this.http.put<any>(`${this.API_URL}/${id}`, body);
  }

  /**
   * Eliminar nota técnica: DELETE /api/tecnicos/{id}
   */
  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.API_URL}/${id}`);
  }

  /**
   * Carga masiva de notas técnicas: POST /api/tecnicos/bulk-upload (en formData con 'file').
   */
  bulkUpload(form: FormData): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/bulk-upload`, form);
  }

  /**
   * Truncar tablas tecnicos + tecnico_session (solo ROLE_ADMIN).
   * POST /api/tecnicos/truncate
   */
  truncate(): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/truncate`, {});
  }
}
