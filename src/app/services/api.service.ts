import { Injectable } from '@angular/core';
import { Observable, tap } from 'rxjs';
import { AuthService } from './auth.service';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  put(arg0: string, editingUser: any) {
    throw new Error('Method not implemented.');
  }
  private apiUrl = 'http://localhost:8000/api';
  private baseUrl = 'http://localhost:8000/api'; // correct


  constructor(private http: HttpClient, private authService: AuthService) { }

  // Headers pour les requêtes HTTP
  private getHeaders(): HttpHeaders {
    const token = this.authService.getToken();
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
    });
  }

  // ==================== AUTHENTIFICATION ====================
  login(email: string, password: string): Observable<any> {
  return this.http.post<any>(`${this.baseUrl}/login`, { email, password }).pipe(
    tap(response => {
      if (response.token) {
        localStorage.setItem('token', response.token);
      }
    })
  );
}


  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, userData, { 
      headers: new HttpHeaders({'Content-Type': 'application/json'}) 
    });
  }

  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/logout`, {}, { headers: this.getHeaders() });
  }

  // ==================== SPÉCIALITÉS ====================
  getSpecialites(): Observable<any> {
    return this.http.get(`${this.apiUrl}/specialites`, { headers: this.getHeaders() });
  }

  getSpecialite(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/specialites/${id}`, { headers: this.getHeaders() });
  }

  createSpecialite(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/specialites`, data, { headers: this.getHeaders() });
  }

  updateSpecialite(id: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/specialites/${id}`, data, { headers: this.getHeaders() });
  }

  deleteSpecialite(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/specialites/${id}`, { headers: this.getHeaders() });
  }

  // ==================== MÉDECINS ====================
  getMedecins(): Observable<any> {
    return this.http.get(`${this.apiUrl}/medecins`, { headers: this.getHeaders() });
  }

  getMedecin(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/medecins/${id}`, { headers: this.getHeaders() });
  }

  createMedecin(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/medecins`, data, { headers: this.getHeaders() });
  }

  updateMedecin(id: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/medecins/${id}`, data, { headers: this.getHeaders() });
  }

  deleteMedecin(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/medecins/${id}`, { headers: this.getHeaders() });
  }

  getMedecinDisponibilites(medecinId: number, date: string): Observable<any> {
    const params = new HttpParams().set('date', date);
    return this.http.get(`${this.apiUrl}/medecins/${medecinId}/disponibilites`, { 
      headers: this.getHeaders(), 
      params 
    });
  }

  // ==================== PATIENTS ====================
  

  // ==================== RENDEZ-VOUS ====================
  getRendezVous(): Observable<any> {
    return this.http.get(`${this.apiUrl}/rendezvous`, { headers: this.getHeaders() });
  }

  getRendezVousById(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/rendezvous/${id}`, { headers: this.getHeaders() });
  }

  // ==================== RENDEZ-VOUS ====================
createRendezVous(data: any): Observable<any> {
  const token = this.authService.getToken();
  console.log('🔐 Token pour createRendezVous:', token); // Debug
  
  const headers = new HttpHeaders({
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  });
// DEBUG: Vérifie le format des données
  console.log('📤 Données envoyées à l\'API:', JSON.stringify(data, null, 2));
  return this.http.post(`${this.apiUrl}/rendezvous`, data, { headers });
}

  updateRendezVous(id: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/rendezvous/${id}`, data, { headers: this.getHeaders() });
  }

  deleteRendezVous(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/rendezvous/${id}`, { headers: this.getHeaders() });
  }

  // ==================== PAIEMENT ====================
  processPayment(paymentData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/paiements`, paymentData, { headers: this.getHeaders() });
  }

  getPaymentStatus(appointmentId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/rendezvous/${appointmentId}/statut-paiement`, { 
      headers: this.getHeaders() 
    });
  }

  // ==================== PDF ====================
  generatePdf(rendezVousId: number): Observable<Blob> {
    return this.http.get(`${this.apiUrl}/rendezvous/${rendezVousId}/generate-pdf`, { 
      headers: this.getHeaders(),
      responseType: 'blob'
    });
  }

  // ==================== PROFIL UTILISATEUR ====================
  getUserProfile(): Observable<any> {
    return this.http.get(`${this.apiUrl}/profile`, { headers: this.getHeaders() });
  }

  updateUserProfile(userData: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/profile`, userData, { headers: this.getHeaders() });
  }

  // ==================== ADMIN ====================
  getAdminStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/admin/stats`, { headers: this.getHeaders() });
  }

 getAllUsers(page: number = 1, perPage: number = 10, searchTerm: string = ''): Observable<any> {
    const params: any = { page, per_page: perPage };
    if (searchTerm) {
      params.search = searchTerm;
    }

    return this.http.get<any>(`${this.baseUrl}/admin/users`, {
      headers: this.authService.getAuthHeaders(),
      params
    });
  }

  // ✅ mise à jour du rôle d’un utilisateur
  updateUserRole(userId: number, newRole: string): Observable<any> {
    return this.http.put<any>(
      `${this.baseUrl}/admin/users/${userId}/role`,
      { role: newRole },
      { headers: this.authService.getAuthHeaders() }
    );
  }


  // ==================== STATISTIQUES ====================
  getUserStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/mes-statistiques`, { headers: this.getHeaders() });
  }

  // Méthode générique pour les requêtes
  private request(method: string, endpoint: string, data?: any): Observable<any> {
    const url = `${this.apiUrl}/${endpoint}`;
    const options = {
      headers: this.getHeaders(),
      body: data
    };
    
    return this.http.request(method, url, options);
  }
  // ==================== PAIEMENT ====================
updatePaiementStatus(appointmentId: number, status: string): Observable<any> {
  const token = this.authService.getToken();
  const headers = new HttpHeaders({
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  });

  return this.http.put(`${this.apiUrl}/rendezvous/${appointmentId}/paiement`, 
    { statut_paiement: status },
    { headers }
  );
}
// ==================== RENDEZ-VOUS AVEC FILTRES ====================
getRendezVousWithFilters(filters: any): Observable<any> {
  const token = this.authService.getToken();
  const headers = new HttpHeaders({
    'Authorization': `Bearer ${token}`
  });

  // Conversion des filtres en params HTTP
  let params = new HttpParams();
  for (const key in filters) {
    if (filters[key]) {
      params = params.set(key, filters[key]);
    }
  }

  return this.http.get(`${this.apiUrl}/rendezvous`, { headers, params });
}

// Statistiques utilisateur
getUserRendezVousStats(): Observable<any> {
  const token = this.authService.getToken();
  const headers = new HttpHeaders({
    'Authorization': `Bearer ${token}`
  });

  return this.http.get(`${this.apiUrl}/mes-rendezvous/stats`, { headers });
}
// Supprimer un utilisateur
deleteUser(id: number): Observable<any> {
  const token = this.authService.getToken();
  const headers = new HttpHeaders({
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  });
  return this.http.delete(`${this.baseUrl}/admin/users/${id}`, { headers });
}

// Mettre à jour un utilisateur (ex: pour édition complète)
updateUser(userId: number, data: any): Observable<any> {
  const token = this.authService.getToken(); // récupère le token depuis AuthService
  const headers = new HttpHeaders({
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  });

  return this.http.put(`${this.baseUrl}/admin/users/${userId}`, data, { headers });
}

// ==================== JUSTIFICATIFS ====================
// ==================== SUIVI JUSTIFICATIFS (ADMIN) ====================

// Liste des justificatifs générés
getJustificatifsAdmin(page: number = 1, limit: number = 10, search: string = ''): Observable<any> {
  let params = new HttpParams()
    .set('page', page.toString())
    .set('limit', limit.toString());
  
  if (search) {
    params = params.set('search', search);
  }

  return this.http.get(`${this.apiUrl}/admin/justificatifs`, { 
    headers: this.getHeaders(), 
    params 
  });
}

// Statistiques des justificatifs
getStatistiquesJustificatifs(): Observable<any> {
  return this.http.get(`${this.apiUrl}/admin/justificatifs/stats`, { 
    headers: this.getHeaders() 
  });
}

// Télécharger un justificatif
downloadJustificatifAdmin(id: number): Observable<Blob> {
  return this.http.get(`${this.apiUrl}/admin/justificatifs/${id}/download`, {
    headers: this.getHeaders(),
    responseType: 'blob'
  });
}

// Détails d'un justificatif
getJustificatifDetails(id: number): Observable<any> {
  return this.http.get(`${this.apiUrl}/admin/justificatifs/${id}`, { 
    headers: this.getHeaders() 
  });
}
}