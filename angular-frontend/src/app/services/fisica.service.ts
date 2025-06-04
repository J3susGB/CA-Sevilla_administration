// src/app/services/fisica.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Fisica {
  id: number;
  nif: string;
  name: string;
  first_surname: string;
  second_surname?: string;
  categoria_id: number;
  categoria: string;
  convocatoria: number;
  repesca: boolean;
  yoyo: number;
  velocidad: number | null;
}

@Injectable({ providedIn: 'root' })
export class FisicaService {
  private readonly API_URL = 'http://localhost:8000/api/fisica';

  constructor(private http: HttpClient) {}

  getAll(): Observable<{ status: string; data: Fisica[] }> {
    return this.http.get<any>(`${this.API_URL}`);
  }

  get(id: number): Observable<{ status: string; data: Fisica }> {
    return this.http.get<any>(`${this.API_URL}/${id}`);
  }

  create(f: Partial<Fisica>): Observable<any> {
    return this.http.post<any>(this.API_URL, f);
  }

  update(id: number, f: Partial<Fisica>): Observable<any> {
    return this.http.put<any>(`${this.API_URL}/${id}`, f);
  }

  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.API_URL}/${id}`);
  }

  bulkUpload(form: FormData): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/bulk-upload`, form);
  }

}
