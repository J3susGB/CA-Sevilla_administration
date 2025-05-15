import { Injectable } from '@angular/core';
import {
  CanActivate,
  ActivatedRouteSnapshot,
  Router,
  UrlTree
} from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({ providedIn: 'root' })
export class RoleGuard implements CanActivate {
  constructor(
    private auth: AuthService,
    private router: Router
  ) {}

  canActivate(route: ActivatedRouteSnapshot): boolean | UrlTree {
    const expectedRoles: string[] = route.data['roles'] ?? [];
    const userRoles = this.auth.getRoles();

    const hasRole = userRoles.some(role =>
      expectedRoles.includes(role)
    );

    if (!hasRole) {
      return this.router.createUrlTree(['/unauthorized']);
    }
    return true;
  }
}
