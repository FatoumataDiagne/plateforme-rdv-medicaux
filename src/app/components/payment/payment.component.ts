import { Component, OnInit, Input, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PaiementService } from '../../services/payment.service';
import { ApiService } from '../../services/api.service';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-payment',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './payment.component.html',
  styleUrls: ['./payment.component.css']
})
export class PaymentComponent implements OnInit, OnChanges {
  @Input() amount: number = 0;
  @Input() appointmentId: number | null = null;
  
  paymentData = {
    cardNumber: '',
    expiryDate: '',
    cvv: '',
    cardHolder: ''
  };
  
  loading = false;
  error = '';
  success = '';
  status: 'en_attente' | 'processing' | 'success' | 'failed' = 'en_attente';
  countdown: number = 5;
  displayedAmount: number = 0;

  constructor(
    private paiementService: PaiementService,
    private apiService: ApiService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this.loadPaymentData();
  }

  ngOnChanges(changes: SimpleChanges): void {
    // Quand les inputs changent (surtout amount)
    if (changes['amount'] && changes['amount'].currentValue) {
      this.displayedAmount = changes['amount'].currentValue;
      console.log('💰 Montant mis à jour:', this.displayedAmount);
    }
    if (changes['appointmentId'] && changes['appointmentId'].currentValue) {
      console.log('📋 ID Rendez-vous mis à jour:', this.appointmentId);
    }
  }

  private loadPaymentData(): void {
    // 1. Vérifier les paramètres de route d'abord
    this.route.queryParams.subscribe(params => {
      console.log('🔍 Query Params:', params);
      
      if (params['appointmentId']) {
        this.appointmentId = +params['appointmentId'];
        console.log('📋 ID from query params:', this.appointmentId);
      }
      
      if (params['amount']) {
        this.displayedAmount = +params['amount'];
        console.log('💰 Amount from query params:', this.displayedAmount);
      }
    });

    // 2. Vérifier le state de navigation ensuite
    const navigation = this.router.getCurrentNavigation();
    if (navigation?.extras.state) {
      console.log('📦 Navigation state:', navigation.extras.state);
      
      if (navigation.extras.state['appointmentId']) {
        this.appointmentId = navigation.extras.state['appointmentId'];
      }
      
      if (navigation.extras.state['amount']) {
        this.displayedAmount = navigation.extras.state['amount'];
      }
    }

    // 3. Utiliser les valeurs @Input en dernier recours
    if (!this.displayedAmount && this.amount) {
      this.displayedAmount = this.amount;
    }

    console.log('✅ Données finales - Montant:', this.displayedAmount, 'ID:', this.appointmentId);
  }

  processPayment(): void {
    console.log('🔄 Début du processus de paiement');
    console.log('📋 ID du rendez-vous:', this.appointmentId);
    console.log('💰 Montant à payer:', this.displayedAmount);

    if (!this.appointmentId) {
      this.error = 'ID de rendez-vous manquant. Veuillez réessayer.';
      console.error('❌ Erreur: appointmentId manquant');
      return;
    }

    if (this.displayedAmount <= 0) {
      this.error = 'Montant invalide. Veuillez réessayer.';
      console.error('❌ Erreur: montant invalide');
      return;
    }

    if (!this.isFormValid()) {
      this.error = 'Veuillez remplir tous les champs correctement';
      return;
    }

    this.loading = true;
    this.status = 'processing';
    this.error = '';
    this.success = '';

    // Simulation du paiement
    this.paiementService.simulerPaiement({
      appointment_id: this.appointmentId,
      amount: this.displayedAmount
    }).subscribe({
      next: (response: any) => {
        console.log('✅ Paiement simulé réussi:', response);
        if (response.success) {
          this.handlePaymentSuccess();
        } else {
          this.handlePaymentFailure('Paiement refusé par la banque');
        }
      },
      error: (err: any) => {
        console.error('❌ Erreur paiement simulé:', err);
        this.handlePaymentFailure('Erreur lors du traitement du paiement');
      }
    });
  }

  private handlePaymentSuccess(): void {
    console.log('✅ Paiement réussi, mise à jour du statut...');
    
    this.apiService.updatePaiementStatus(this.appointmentId!, 'paye').subscribe({
      next: (updateResponse: any) => {
        console.log('✅ Statut mis à jour:', updateResponse);
        this.loading = false;
        this.status = 'success';
        this.success = 'Paiement réussi ! Votre rendez-vous est confirmé.';
        
        this.startCountdown();
        this.generateJustificatif();
      },
      error: (updateError: any) => {
        console.error('❌ Erreur mise à jour statut:', updateError);
        this.handlePaymentFailure('Erreur lors de la mise à jour du statut');
      }
    });
  }

  private handlePaymentFailure(errorMessage: string): void {
    console.log('❌ Paiement échoué, mise à jour statut...');
    
    this.apiService.updatePaiementStatus(this.appointmentId!, 'echoue').subscribe({
      next: () => {
        console.log('✅ Statut échec mis à jour');
        this.finalizeError(errorMessage);
      },
      error: (err) => {
        console.error('❌ Erreur mise à jour statut échec:', err);
        this.finalizeError(errorMessage);
      }
    });
  }

  private finalizeError(errorMessage: string): void {
    this.loading = false;
    this.status = 'failed';
    this.error = errorMessage;
  }

  private startCountdown(): void {
    const interval = setInterval(() => {
      this.countdown--;
      if (this.countdown <= 0) {
        clearInterval(interval);
        this.router.navigate(['/mes-rendezvous']);
      }
    }, 1000);
  }

  private generateJustificatif(): void {
    if (!this.appointmentId) return;
    
    this.apiService.generatePdf(this.appointmentId).subscribe({
      next: (pdfBlob: Blob) => {
        this.downloadPdf(pdfBlob);
      },
      error: (err: any) => {
        console.warn('⚠️ PDF non généré, mais paiement validé');
      }
    });
  }

  private downloadPdf(pdfBlob: Blob): void {
    const url = window.URL.createObjectURL(pdfBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `justificatif-rdv-${this.appointmentId}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  }

  private isFormValid(): boolean {
    return this.paymentData.cardNumber.replace(/\s/g, '').length === 16 &&
           this.paymentData.expiryDate.length === 5 &&
           this.paymentData.cvv.length === 3 &&
           this.paymentData.cardHolder.length > 3;
  }

  formatCardNumber(event: any): void {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 16) value = value.substring(0, 16);
    
    const formatted = value.replace(/(\d{4})/g, '$1 ').trim();
    this.paymentData.cardNumber = formatted;
    this.clearError();
  }

  formatExpiryDate(event: any): void {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 4) value = value.substring(0, 4);
    
    if (value.length > 2) {
      value = value.substring(0, 2) + '/' + value.substring(2);
    }
    
    this.paymentData.expiryDate = value;
    this.clearError();
  }

  formatCvv(event: any): void {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 3) value = value.substring(0, 3);
    this.paymentData.cvv = value;
    this.clearError();
  }

  private clearError(): void {
    if (this.error) this.error = '';
  }

  retryPayment(): void {
    this.status = 'en_attente';
    this.error = '';
    this.countdown = 5;
  }

  backToAppointments(): void {
    this.router.navigate(['/mes-rendezvous']);
  }

  getHeaderClass(): string {
    switch (this.status) {
      case 'success': return 'bg-success-gradient';
      case 'failed': return 'bg-danger-gradient';
      case 'processing': return 'bg-warning-gradient';
      default: return 'bg-primary-gradient';
    }
  }

  getHeaderTitle(): string {
    switch (this.status) {
      case 'success': return '✅ Paiement Réussi';
      case 'failed': return '❌ Paiement Échoué';
      case 'processing': return '⏳ Traitement en Cours';
      default: return '💳 Paiement Sécurisé';
    }
  }

  getStatusText(): string {
    switch (this.status) {
      case 'en_attente': return 'En attente de paiement';
      case 'processing': return 'Traitement en cours...';
      case 'success': return `Paiement confirmé • Redirection dans ${this.countdown}s`;
      case 'failed': return 'Échec du paiement';
      default: return this.status;
    }
  }
}