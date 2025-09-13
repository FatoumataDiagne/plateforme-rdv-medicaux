import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject } from 'rxjs';
import { tap } from 'rxjs/operators';


@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost:8000/api';
  private currentUserSubject = new BehaviorSubject<any>(null);
  public currentUser = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient) {
    // Charge les données au démarrage
    const token = localStorage.getItem('auth_token');
    const user = localStorage.getItem('current_user');
    if (token && user) {
      try {
        this.currentUserSubject.next(JSON.parse(user));
      } catch (e) {
        console.error('Error parsing user data', e);
        this.clearStorage();
      }
    }
  }

  // ✅ REGISTER - Doit retourner un Observable
  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, userData);
  }
getAuthHeaders(): HttpHeaders {
  const token = this.getToken();
  return new HttpHeaders({
    'Content-Type': 'application/json',
    'Authorization': token ? `Bearer ${token}` : ''
  });
}

 
  // LOGIN - Version corrigée
  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials).pipe(
      tap((response: any) => {
        console.log('✅ Réponse complète login:', response);
        
        // STOCKE LE TOKEN (plusieurs formats possibles)
        let token = response.token || response.access_token || response.data?.token;
        
        if (token) {
          localStorage.setItem('auth_token', token);
          console.log('🔐 Token stocké:', token);
        } else {
          console.error('❌ Aucun token trouvé dans la réponse:', response);
          throw new Error('Token manquant dans la réponse');
        }

        // STOCKE L'UTILISATEUR (plusieurs formats possibles)
        let userData = response.user || response.data?.user || response.data;
        
        if (userData) {
          localStorage.setItem('current_user', JSON.stringify(userData));
          this.currentUserSubject.next(userData);
          console.log('👤 User stocké:', userData);
        } else {
          console.error('❌ Aucun user trouvé dans la réponse:', response);
        }
      })
    );
  }



  // ✅ LOGOUT
  logout(): Observable<any> {
  return this.http.post(`${this.apiUrl}/logout`, {}, { headers: this.getAuthHeaders() }).pipe(
    tap(() => {
      this.clearStorage();
      this.currentUserSubject.next(null);
    })
  );
}


  // ✅ UPDATE PROFILE
updateProfile(userData: any): Observable<any> {
  return this.http.put(`${this.apiUrl}/profile`, userData, { headers: this.getAuthHeaders() });
}
  // ✅ GET CURRENT USER
  getCurrentUser(): any {
    return this.currentUserSubject.value;
  }

  // ✅ IS LOGGED IN
  isLoggedIn(): boolean {
    return !!localStorage.getItem('auth_token');
  }

  // ✅ GET TOKEN
  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  // ✅ CLEAR STORAGE (méthode helper)
  private clearStorage(): void {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('current_user');
  }
  // Ajoutez ces méthodes dans votre AuthService
isPatient(): boolean {
  return this.getCurrentUser()?.role === 'patient';
}

isMedecin(): boolean {
  return this.getCurrentUser()?.role === 'medecin';
}

isAdmin(): boolean {
  return this.getCurrentUser()?.role === 'admin';
}
  
 
}