import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Sancion {
  id?: number;
  nif: string;
  name?: string;
  first_surname?: string;
  second_surname?: string;
  categoria?: string;
  categoria_id?: number;
  fecha: string;
  tipo: string;
  nota: number;
}

@Injectable({ providedIn: 'root' })
export class SancionService {
  private readonly API_URL = 'http://localhost:8000/api/sanciones';

  constructor(private http: HttpClient) {}

  getAll(): Observable<{ status: string; data: Sancion[] }> {
    return this.http.get<any>(`${this.API_URL}`);
  }

  get(id: number): Observable<{ status: string; data: Sancion }> {
    return this.http.get<any>(`${this.API_URL}/${id}`);
  }

  create(s: Partial<Sancion>): Observable<any> {
    return this.http.post<any>(this.API_URL, s);
  }

  update(id: number, s: Partial<Sancion>): Observable<any> {
    return this.http.put<any>(`${this.API_URL}/${id}`, s);
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
