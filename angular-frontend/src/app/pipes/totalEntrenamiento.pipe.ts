// src/app/pipes/totalEntrenamiento.pipe.ts

import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'totalEntrenamiento',
  standalone: true
})
export class TotalEntrenamientoPipe implements PipeTransform {
  transform(value: any): number {
    if (!value) return 0;

    return ['septiembre','octubre','noviembre','diciembre','enero','febrero','marzo','abril']
      .map(mes => value[mes] || 0)
      .reduce((a, b) => a + b, 0);
  }
}
