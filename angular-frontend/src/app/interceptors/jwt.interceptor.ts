// src/app/interceptors/jwt.interceptor.ts
import { HttpInterceptorFn, HttpRequest, HttpHandlerFn } from '@angular/common/http';
import { tap } from 'rxjs/operators';

export const jwtInterceptor: HttpInterceptorFn = (req, next) => {
  // 1) Tomamos el token de localStorage
  const token = localStorage.getItem('auth_token');
  //console.log('[jwtInterceptor] token encontrado:', token);

  // 2) Si existe, clonamos la petición y seteamos el header
  const authReq = token
    ? (req as HttpRequest<unknown>).clone({
        setHeaders: {
          Authorization: `Bearer ${token}`
        }
      })
    : req;

      /*
    console.log(
    '[jwtInterceptor] cabecera Authorization en la petición:',
    authReq.headers.get('Authorization')
  );
  */

  // 3) Continuamos el pipeline
  return next(authReq).pipe(
    tap({
      next: () => {},
      error: err => console.error('[jwtInterceptor] error response:', err)
    })
  );
};
