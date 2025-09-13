import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthService } from './auth.service';

export interface DashboardStats {
  general: {
    total_users: number;
    total_patients: number;
    total_medecins: number;
    total_admins: number;
    total_specialites: number;
    total_rendezvous: number;
    revenus_totaux: number;
  };
  par_statut: {
    rendezvous: {
      confirme: number;
      en_attente: number;
      annule: number;
    };
    paiements: {
      paye: number;
      en_attente: number;
      rembourse: number;
    };
  };
  advanced: {
    finances: {
      paiements_en_ligne: number;
      paiements_sur_place: number;
      revenus_en_ligne: number;
      revenus_sur_place: number;
    };
    specialites: Array<{
      id: number;
      name: string;
      medecins_count: number;
      rendez_vous_count: number;
      revenus: number;
    }>;
    // AJOUTEZ CETTE SECTION POUR LES JUSTIFICATIFS
    justificatifs: {
      total: number;
      telecharges: number;
      taux_utilisation: number;
      par_mois: Array<{
        mois: string;
        count: number;
      }>;
  };
}
}

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient, private authService: AuthService) {}

  private getHeaders(): HttpHeaders {
    const token = this.authService.getToken();
    return new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });
  }

  // Méthodes existantes
  getDashboardStats(): Observable<{success: boolean; data: DashboardStats}> {
    return this.http.get<{success: boolean; data: DashboardStats}>(
      `${this.apiUrl}/admin/stats`,
      { headers: this.getHeaders() }
    );
  }

  getUsers(page: number = 1, perPage: number = 10): Observable<any> {
    return this.http.get(
      `${this.apiUrl}/admin/users?page=${page}&per_page=${perPage}`,
      { headers: this.getHeaders() }
    );
  }

  getRendezVous(filters?: any): Observable<any> {
    let url = `${this.apiUrl}/admin/rendezvous`;
    if (filters) {
      const params = new URLSearchParams();
      Object.keys(filters).forEach(key => {
        if (filters[key]) params.append(key, filters[key]);
      });
      url += `?${params.toString()}`;
    }
    return this.http.get(url, { headers: this.getHeaders() });
  }

  // Nouvelles méthodes pour les composants
  getSpecialites(): Observable<any> {
    return this.http.get(
      `${this.apiUrl}/admin/specialites`,
      { headers: this.getHeaders() }
    );
  }

  createSpecialite(specialiteData: any): Observable<any> {
    return this.http.post(
      `${this.apiUrl}/admin/specialites`,
      specialiteData,
      { headers: this.getHeaders() }
    );
  }

  updateSpecialite(specialiteId: number, specialiteData: any): Observable<any> {
    return this.http.put(
      `${this.apiUrl}/admin/specialites/${specialiteId}`,
      specialiteData,
      { headers: this.getHeaders() }
    );
  }

  deleteSpecialite(specialiteId: number): Observable<any> {
    return this.http.delete(
      `${this.apiUrl}/admin/specialites/${specialiteId}`,
      { headers: this.getHeaders() }
    );
  }

  getStatistiques(): Observable<any> {
    return this.http.get(
      `${this.apiUrl}/admin/statistiques`,
      { headers: this.getHeaders() }
    );
  }

  getPaiements(page: number = 1, perPage: number = 10): Observable<any> {
    return this.http.get(
      `${this.apiUrl}/admin/paiements?page=${page}&per_page=${perPage}`,
      { headers: this.getHeaders() }
    );
  }

  getStatistiquesPaiements(): Observable<any> {
    return this.http.get(
      `${this.apiUrl}/admin/statistiques/paiements`,
      { headers: this.getHeaders() }
    );
  }
  // Dans admin.service.ts
getJustificatifs(page: number = 1, perPage: number = 10): Observable<any> {
  return this.http.get(
    `${this.apiUrl}/admin/justificatifs?page=${page}&per_page=${perPage}`,
    { headers: this.getHeaders() }
  );
}

getStatistiquesJustificatifs(): Observable<any> {
  return this.http.get(
    `${this.apiUrl}/admin/statistiques/justificatifs`,
    { headers: this.getHeaders() }
  );
}

downloadJustificatif(id: number): Observable<any> {
  return this.http.get(
    `${this.apiUrl}/admin/justificatifs/${id}/download`,
    { 
      headers: this.getHeaders(),
      responseType: 'blob'
    }
  );
}
}