import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Asistencia {
  id?: number;
  arbitro_id: number;
  nif: string;
  asiste: boolean;
  categoria_id: number;
  name: string;
  first_surname: string;
  second_surname?: string;
}

export interface Sesion {
  id: number;
  fecha: string;        // "25-05-2025"
  tipo: string;         // "teorica" | "practica"
  categoria: string;    // "Provincial", etc.
  asistencias: Asistencia[];
}

@Injectable({ providedIn: 'root' })
export class AsistenciaService {
  private readonly API_URL = 'http://localhost:8000/api/asistencias';

  constructor(private http: HttpClient) {}

  listAll(): Observable<{ status: string; data: Sesion[] }> {
    return this.http.get<any>(`${this.API_URL}`);
  }
  get(id: number): Observable<{ status: string; data: Sesion }> {
    return this.http.get<any>(`${this.API_URL}/${id}`);
  }
  totals(): Observable<{ status: string; data: any[] }> {
    return this.http.get<any>(`${this.API_URL}/totals`);
  }
  create(body: any): Observable<any> {
    return this.http.post<any>(this.API_URL, body);
  }
  update(id: number, body: any): Observable<any> {
    return this.http.put<any>(`${this.API_URL}/${id}`, body);
  }
  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.API_URL}/${id}`);
  }
  bulkUpload(form: FormData): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/bulk-upload`, form);
  }
  truncate(): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/truncate`, {});
  }
}
