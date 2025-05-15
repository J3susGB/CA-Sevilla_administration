// src/app/app.config.ts

import { ApplicationConfig, importProvidersFrom } from '@angular/core';
import { provideZoneChangeDetection }           from '@angular/core';
import { provideRouter }                        from '@angular/router';
import { provideHttpClient, withInterceptors }  from '@angular/common/http';
import { ReactiveFormsModule }                  from '@angular/forms';

import { routes }           from './app.routes';
import { jwtInterceptor }   from './interceptors/jwt.interceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    provideZoneChangeDetection({ eventCoalescing: true }),

    provideRouter(routes),

    provideHttpClient(
      withInterceptors([ jwtInterceptor ])
    ),

    importProvidersFrom(ReactiveFormsModule),
  ]
};
