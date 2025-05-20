// src/app/app.config.ts

import { ApplicationConfig, importProvidersFrom }       from '@angular/core';
import { provideZoneChangeDetection }                   from '@angular/core';
import { provideRouter }                                from '@angular/router';
import { provideHttpClient, withInterceptors }          from '@angular/common/http';
import { ReactiveFormsModule }                          from '@angular/forms';
import { BrowserAnimationsModule }                      from '@angular/platform-browser/animations';
import { MatDialogModule }                              from '@angular/material/dialog';
import { MatTabsModule }                                from '@angular/material/tabs';
import { MatFormFieldModule }                           from '@angular/material/form-field';
import { MatInputModule }                               from '@angular/material/input';
import { MatSelectModule }                              from '@angular/material/select';
import { MatButtonModule }                              from '@angular/material/button';

import { routes }           from './app.routes';
import { jwtInterceptor }   from './interceptors/jwt.interceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    provideZoneChangeDetection({ eventCoalescing: true }),

    provideRouter(routes),

    provideHttpClient(
      withInterceptors([ jwtInterceptor ])
    ),

    importProvidersFrom(
      ReactiveFormsModule,
      BrowserAnimationsModule,
      MatDialogModule,
      MatTabsModule,
      MatFormFieldModule,
      MatInputModule,
      MatSelectModule,
      MatButtonModule
    ),
  ]
};
