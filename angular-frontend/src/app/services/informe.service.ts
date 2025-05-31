// src/app/services/informe.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Informe {
  id: number;
  arbitro_id: number;
  nif: string;
  name: string;
  first_surname: string;
  second_surname?: string;
  categoria: string;
  categoria_id: number;
  fecha: string;
  nota: number;
}

@Injectable({ providedIn: 'root' })
export class InformeService {
  private readonly API_URL = 'http://localhost:8000/api/informes';

  constructor(private http: HttpClient) {}

  getAll(): Observable<{ status: string; data: Informe[] }> {
    return this.http.get<any>(`${this.API_URL}`);
  }

  create(data: Partial<Informe>): Observable<any> {
    return this.http.post(this.API_URL, data);
  }

  update(id: number, data: Partial<Informe>): Observable<any> {
    return this.http.put(`${this.API_URL}/${id}`, data);
  }

  delete(id: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/${id}`);
  }

  bulkUpload(form: FormData): Observable<any> {
    return this.http.post(`${this.API_URL}/bulk-upload`, form);
  }
}
