// src/app/app.routes.ts

import { Routes } from '@angular/router';

import { LoginComponent }                    from './login/login.component';
import { UnauthorizedComponent }             from './auth/unauthorized.component';

import { AuthGuard }                         from './guards/auth.guard';
import { RoleGuard }                         from './guards/role.guard';

import { DashboardAdminComponent }           from './dashboard-admin/dashboard-admin.component';
import { DashboardCapacitacionComponent }    from './dashboard-capacitacion/dashboard-capacitacion.component';
import { DashboardClasificacionComponent }   from './dashboard-clasificacion/dashboard-clasificacion.component';
import { DashboardInformacionComponent }     from './dashboard-informacion/dashboard-informacion.component';

export const routes: Routes = [
  { path: '', component: LoginComponent },

  {
    path: 'admin',
    component: DashboardAdminComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN'] }
  },
  {
    path: 'capacitacion',
    component: DashboardCapacitacionComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_CAPACITACION'] }
  },
  {
    path: 'clasificacion',
    component: DashboardClasificacionComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_CLASIFICACION'] }
  },
  {
    path: 'informacion',
    component: DashboardInformacionComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_INFORMACION'] }
  },

  { path: 'unauthorized', component: UnauthorizedComponent },
  { path: '**', redirectTo: '' }
];
