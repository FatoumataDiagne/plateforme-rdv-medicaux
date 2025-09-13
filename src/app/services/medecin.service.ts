// src/app/services/medecin.service.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpParams, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthService } from './auth.service';

export interface PlageHoraire {
  id?: number;
  medecin_id: number;
  jour_semaine: string;
  heure_debut: string;
  heure_fin: string;
  created_at?: string;
  updated_at?: string;
}

export interface RendezVous {
  id: number;
  date: string;
  heure_debut: string;
  heure_fin: string;
  statut: string;
  statut_paiement: string;
  mode_paiement: string;
  patient: {
    id: number;
    prenom: string;
    nom: string;
    telephone?: string;
    email?: string;
  };
  specialite: {
    id: number;
    nom: string;
  };
  montant: number;
}

@Injectable({
  providedIn: 'root'
})
export class MedecinService {
  private apiUrl = 'http://localhost:8000/api/medecin';

  constructor(private http: HttpClient, private authService: AuthService) { }

  // Méthode helper pour obtenir les headers avec le token
  private getAuthHeaders(): HttpHeaders {
    const token = this.authService.getToken();
    return new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    });
  }

  // Récupérer les rendez-vous à confirmer
  getRendezVousAConfirmer(): Observable<RendezVous[]> {
    const headers = this.getAuthHeaders();
    return this.http.get<RendezVous[]>(`${this.apiUrl}/rendezvous-a-confirmer`, { headers });
  }

  // Confirmer un rendez-vous - CHANGÉ DE POST À PUT
  confirmerRendezVous(id: number): Observable<any> {
    const headers = this.getAuthHeaders();
    return this.http.put(`${this.apiUrl}/rendezvous/${id}/confirmer`, {}, { headers });
  }

  // Refuser un rendez-vous - CHANGÉ DE POST À PUT
  refuserRendezVous(id: number, raison: string): Observable<any> {
    const headers = this.getAuthHeaders();
    return this.http.put(`${this.apiUrl}/rendezvous/${id}/refuser`, { raison }, { headers });
  }

  // Générer un justificatif PDF
  genererJustificatif(id: number): Observable<Blob> {
    const headers = this.getAuthHeaders();
    return this.http.get(`${this.apiUrl}/rendezvous/${id}/justificatif`, {
      headers,
      responseType: 'blob'
    });
  }

  // Récupérer l'agenda du médecin
  getAgenda(dateDebut: string, dateFin: string): Observable<RendezVous[]> {
    const headers = this.getAuthHeaders();
    let params = new HttpParams()
      .set('date_debut', dateDebut)
      .set('date_fin', dateFin);
      
    return this.http.get<RendezVous[]>(`${this.apiUrl}/agenda`, { headers, params });
  }

  // Les autres méthodes pour les plages horaires restent inchangées
  getPlagesHoraires(): Observable<PlageHoraire[]> {
    const headers = this.getAuthHeaders();
    return this.http.get<PlageHoraire[]>(`${this.apiUrl}/plages-horaires`, { headers });
  }

  addPlageHoraire(plage: PlageHoraire): Observable<PlageHoraire> {
    const headers = this.getAuthHeaders();
    return this.http.post<PlageHoraire>(`${this.apiUrl}/plages-horaires`, plage, { headers });
  }

  updatePlageHoraire(id: number, plage: PlageHoraire): Observable<PlageHoraire> {
    const headers = this.getAuthHeaders();
    return this.http.put<PlageHoraire>(`${this.apiUrl}/plages-horaires/${id}`, plage, { headers });
  }

  deletePlageHoraire(id: number): Observable<any> {
    const headers = this.getAuthHeaders();
    return this.http.delete(`${this.apiUrl}/plages-horaires/${id}`, { headers });
  }
}