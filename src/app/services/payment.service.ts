import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, of, delay } from 'rxjs';
import { AuthService } from './auth.service';

@Injectable({
  providedIn: 'root'
})
export class PaiementService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(
    private http: HttpClient,
    private authService: AuthService
  ) { }

  // Simulation de paiement (sans API externe)
  simulerPaiement(paymentData: any): Observable<any> {
    const token = this.authService.getToken();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });

    // Simulation avec délai aléatoire
    const isSuccess = Math.random() > 0.2; // 80% de succès

    return of({
      success: isSuccess,
      message: isSuccess ? 'Paiement réussi' : 'Paiement échoué',
      transactionId: isSuccess ? 'TRX_' + Math.random().toString(36).substr(2, 9) : null,
      status: isSuccess ? 'payé' : 'échoué'
    }).pipe(delay(2000)); // Simule un délai de 2 secondes
  }

  // Vraie intégration Stripe (pour plus tard)
  creerIntentStripe(amount: number): Observable<any> {
    const token = this.authService.getToken();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });

    return this.http.post(`${this.apiUrl}/creer-paiement-intent`, 
      { amount }, 
      { headers }
    );
  }

  // Confirmer le paiement après simulation
  confirmerPaiement(transactionData: any): Observable<any> {
    const token = this.authService.getToken();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    });

    return this.http.post(`${this.apiUrl}/paiements`, 
      transactionData, 
      { headers }
    );
  }
}