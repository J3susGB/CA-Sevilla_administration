// src/app/shared/components/toast/toast.component.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule }    from '@angular/common';
import { ToastService, Toast } from '../../services/toast.service';

@Component({
  selector: 'app-toast',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './toast.component.html',
  styleUrls: ['./toast.component.css']
})
export class ToastComponent implements OnInit {
  toasts: Toast[] = [];         // ← usa el Toast del servicio

  constructor(private toastService: ToastService) {}

  ngOnInit() {
    this.toastService.toasts$.subscribe((ts: Toast[]) => {
      this.toasts = ts;
    });
  }

  dismissToast(id: number) {
    this.toastService.removeToast(id);
  }
}
