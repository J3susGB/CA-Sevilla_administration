// src/app/shared/services/toast.service.ts
import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

export interface Toast {
  id: number;
  message: string;
  type: 'success' | 'error' | 'info';
  duration: number;            // duración en milisegundos
}

@Injectable({ providedIn: 'root' })
export class ToastService {
  private toasts: Toast[] = [];
  private toastsSubject = new Subject<Toast[]>();
  toasts$ = this.toastsSubject.asObservable();

  private lastId = 0;

  /**
   * Muestra un toast con mensaje, tipo y duración (por defecto 3 000 ms).
   * Se autodestruye pasado ese tiempo.
   */
  show(
    message: string,
    type: Toast['type'] = 'info',
    duration: number = 3000     
  ) {
    const id = ++this.lastId;
    const toast: Toast = { id, message, type, duration };
    this.toasts.push(toast);
    this.toastsSubject.next(this.toasts);

    // Programamos la eliminación automática
    setTimeout(() => {
      this.removeToast(id);
    }, duration);
  }

  removeToast(id: number) {
    this.toasts = this.toasts.filter(t => t.id !== id);
    this.toastsSubject.next(this.toasts);
  }
}
