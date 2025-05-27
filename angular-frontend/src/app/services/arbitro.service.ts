import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Arbitro {
  id: number;
  nif: string;
  name: string;
  first_surname: string;
  second_surname?: string;
  categoria_id: number;
  categoria_name?: string;  // lo usaremos para mapear el nombre
}

@Injectable({ providedIn: 'root' })
export class ArbitroService {
  private readonly API_URL = 'http://localhost:8000/api/arbitros';

  constructor(private http: HttpClient) {}

  /** Listado paginado de árbitros */
  getAll(page = 1, limit = 25): Observable<{ status: string; data: Arbitro[]; meta: any }> {
    return this.http.get<any>(`${this.API_URL}?page=${page}&limit=${limit}`);
  }

  /** Obtener un árbitro por ID */
  get(id: number): Observable<{ status: string; data: Arbitro }> {
    return this.http.get<any>(`${this.API_URL}/${id}`);
  }

  /** Crear nuevo árbitro */
  create(arbitro: Partial<Arbitro>): Observable<any> {
    return this.http.post<any>(this.API_URL, arbitro);
  }

  /** Actualizar árbitro existente */
  update(id: number, arbitro: Partial<Arbitro>): Observable<any> {
    return this.http.put<any>(`${this.API_URL}/${id}`, arbitro);
  }

  /** Eliminar árbitro */
  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.API_URL}/${id}`);
  }

  /** Carga masiva */
  bulkUpload(form: FormData): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/bulk-upload`, form);
  }
}
