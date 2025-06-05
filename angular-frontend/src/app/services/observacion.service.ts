import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Observacion {
  id: number;
  codigo: string;
  descripcion: string;
  categoria_id: number;
  categoria: string;
}

@Injectable({ providedIn: 'root' })
export class ObservacionService {
  private readonly API_URL = 'http://localhost:8000/api/observaciones';

  constructor(private http: HttpClient) {}

  getAll(): Observable<{ status: string; data: Observacion[] }> {
    return this.http.get<any>(this.API_URL);
  }

  get(id: number): Observable<{ status: string; data: Observacion }> {
    return this.http.get<any>(`${this.API_URL}/${id}`);
  }

  create(obs: Partial<Observacion>): Observable<any> {
    return this.http.post<any>(this.API_URL, obs);
  }

  update(id: number, obs: Partial<Observacion>): Observable<any> {
    return this.http.put<any>(`${this.API_URL}/${id}`, obs);
  }

  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.API_URL}/${id}`);
  }

  bulkUpload(form: FormData): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/bulk-upload`, form);
  }
}
