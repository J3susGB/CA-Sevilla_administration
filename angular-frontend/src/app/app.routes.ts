// src/app/app.routes.ts
import { Routes } from '@angular/router';

import { LoginComponent }                  from './login/login.component';
import { UnauthorizedComponent }           from './auth/unauthorized.component';
import { AuthGuard }                       from './guards/auth.guard';
import { RoleGuard }                       from './guards/role.guard';
import { DashboardAdminComponent }         from './dashboard-admin/dashboard-admin.component';
import { UsersListComponent }              from './dashboard-admin/users/users-list.component';
import { ArbitrosListComponent }           from './dashboard-admin/arbitros/arbitros-list.component';
import { DashboardCapacitacionComponent }  from './dashboard-capacitacion/dashboard-capacitacion.component';
import { DashboardClasificacionComponent } from './dashboard-clasificacion/dashboard-clasificacion.component';
import { DashboardInformacionComponent }   from './dashboard-informacion/dashboard-informacion.component';
import { CategoriasListComponent } from './dashboard-admin/categorias/categorias-list.component';
import { BonificacionesListComponent } from './dashboard-admin/bonificaciones/bonificaciones-list.component';
import { SesionesListComponent } from './dashboard-admin/sesiones/sesiones-list.component';

export const routes: Routes = [
  // Login público
  { path: '', component: LoginComponent, pathMatch: 'full'  },

  // Unauthorized
  { path: 'unauthorized', component: UnauthorizedComponent },

  // Lista plana de Usuarios 
  {
    path: 'admin/users',
    component: UsersListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN'] }
  },

  // Árbitros
  {
    path: 'admin/arbitros',
    component: ArbitrosListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN','ROLE_CAPACITACION'] }
  },

  // Categorias
  {
    path: 'admin/categorias',
    component: CategoriasListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN'] }
  },

  // Bonificaciones
  {
    path: 'admin/bonificaciones',
    component: BonificacionesListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN','ROLE_CAPACITACION'] }
  },

  // Asistencia a clases y prácticas
  {
    path: 'admin/asistencias',
    component: SesionesListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN','ROLE_CAPACITACION'] }
  },

  // Dashboard principal de Admin (cuando la URL sea EXACTAMENTE "/admin")
  {
    path: 'admin',
    component: DashboardAdminComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN'] },
    pathMatch: 'full'
  },

  // Otros dashboards por rol
  {
    path: 'capacitacion',
    component: DashboardCapacitacionComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN','ROLE_CAPACITACION'] }
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

  // Wildcard al final
  { path: '**', redirectTo: '' }
];
