import { Routes } from '@angular/router';
import { LoginComponent } from './login/login.component';
import { DashboardAdminComponent } from './dashboard_admin/dashboard_admin.component'; // Importar Dashboard Admin

export const routes: Routes = [
  { path: '', component: LoginComponent }, // login en la raíz "/"
  { path: 'dashboard', component: DashboardAdminComponent }, // Ruta dashboard admin

];