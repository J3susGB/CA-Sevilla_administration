import { Routes } from '@angular/router';

import { LoginComponent } from './login/login.component';
import { UnauthorizedComponent } from './auth/unauthorized.component';
import { AuthGuard } from './guards/auth.guard';
import { RoleGuard } from './guards/role.guard';
import { DashboardAdminComponent } from './dashboard-admin/dashboard-admin.component';
import { UsersListComponent } from './dashboard-admin/users/users-list.component';
import { ArbitrosListComponent } from './dashboard-admin/arbitros/arbitros-list.component';
import { DashboardCapacitacionComponent } from './dashboard-capacitacion/dashboard-capacitacion.component';
import { DashboardClasificacionComponent } from './dashboard-clasificacion/dashboard-clasificacion.component';
import { DashboardInformacionComponent } from './dashboard-informacion/dashboard-informacion.component';
import { CategoriasListComponent } from './dashboard-admin/categorias/categorias-list.component';
import { BonificacionesListComponent } from './dashboard-admin/bonificaciones/bonificaciones-list.component';
import { SesionesListComponent } from './dashboard-admin/sesiones/sesiones-list.component';
import { TestsListComponent } from './dashboard-admin/tests/tests-list.component';
import { TecnicosListComponent } from './dashboard-admin/tecnicos/tecnicos-list.component';
import { InformesListComponent } from './dashboard-admin/informes/informes-list.component';
import { SancionesListComponent } from './dashboard-admin/sanciones/sanciones-list.component';
import { EntrenamientosListComponent } from './dashboard-admin/entrenamientos/entrenamientos-list.component';
import { SimulacrosListComponent } from './dashboard-admin/simulacros/simulacros-list.component';
import { FisicaListComponent } from './dashboard-admin/fisica/fisica-list.component';
import { ObservacionService } from './services/observacion.service';
import { ObservacionesListComponent } from './dashboard-admin/observaciones/observaciones-list.component';


export const routes: Routes = [
  { path: '', component: LoginComponent, pathMatch: 'full' },
  { path: 'unauthorized', component: UnauthorizedComponent },
  {
    path: 'admin/users',
    component: UsersListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN'] }
  },
  {
    path: 'admin/arbitros',
    component: ArbitrosListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CAPACITACION'] }
  },
  {
    path: 'admin/categorias',
    component: CategoriasListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN'] }
  },
  {
    path: 'admin/bonificaciones',
    component: BonificacionesListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CAPACITACION'] }
  },
  {
    path: 'admin/asistencias',
    component: SesionesListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CAPACITACION'] }
  },
  {
    path: 'admin/tecnicos',
    component: TecnicosListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CAPACITACION'] }
  },
  {
    path: 'admin/tests',
    component: TestsListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CAPACITACION'] }
  },

  {
    path: 'admin/informes',
    component: InformesListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CLASIFICACION', 'ROLE_INFORMACION'] }
  },

  {
    path: 'admin/sanciones',
    component: SancionesListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CLASIFICACION', 'ROLE_INFORMACION'] }
  },
  {
    path: 'admin/entrenamientos',
    component: EntrenamientosListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { 
      roles: ['ROLE_ADMIN', 'ROLE_CLASIFICACION'],
      animation: 'Entrenamientos'
    }
  },
  {
    path: 'admin/simulacros',
    component: SimulacrosListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { 
      roles: ['ROLE_ADMIN', 'ROLE_CLASIFICACION'],
      animation: 'Simulacros'
    }
  },
  {
    path: 'admin/fisicas',
    component: FisicaListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CLASIFICACION'] }
  },
  {
    path: 'admin/observaciones',
    component: ObservacionesListComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CLASIFICACION'] }
  },
  {
    path: 'admin',
    component: DashboardAdminComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN'] },
    pathMatch: 'full'
  },
  {
    path: 'capacitacion',
    component: DashboardCapacitacionComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_ADMIN', 'ROLE_CAPACITACION'] }
  },
  {
    path: 'clasificacion',
    component: DashboardClasificacionComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_CLASIFICACION', 'ROLE_ADMIN'] }
  },
  {
    path: 'informacion',
    component: DashboardInformacionComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['ROLE_INFORMACION', 'ROLE_ADMIN'] }
  },
  { path: '**', redirectTo: '' }
];
