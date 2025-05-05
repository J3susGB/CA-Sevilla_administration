// angular-frontend/src/app/app.routes.ts

import { Routes }                   from '@angular/router';
import { LoginComponent }           from './login/login.component';
import { DashboardAdminComponent }  from './dashboard_admin/dashboard_admin.component';
import { AuthGuard }                from './guards/auth.guard';

export const routes: Routes = [
  { path: '',          component: LoginComponent },
  { path: 'dashboard', component: DashboardAdminComponent, canActivate: [AuthGuard] },
  { path: '**',        redirectTo: '' }
];
