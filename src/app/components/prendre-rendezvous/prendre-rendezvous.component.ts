import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { PaymentComponent } from '../payment/payment.component'; 
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router'; 

@Component({
  selector: 'app-prendre-rendezvous',
  standalone: true,
  imports: [CommonModule, FormsModule, PaymentComponent],
  templateUrl: './prendre-rendezvous.component.html',
  styleUrls: ['./prendre-rendezvous.component.css'],
})
export class PrendreRendezVousComponent implements OnInit {
  newRendezVousId: number | null = null;
  medecins: any[] = [];
  patients: any[] = [];
  loading: boolean = true;
  error: string = '';
  success: string = '';

  // Formulaire data
  formData = {
    patient_id: '',
    medecin_id: '',
    date: '',
    heure_debut: '09:00',
    heure_fin: '10:00',
    mode_paiement: 'sur_place',
    notes: ''
  };

  heuresDisponibles = [
    '08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'
  ];

  constructor(
    private apiService: ApiService,
    private authService: AuthService,
    private router: Router,
  ) { }

  ngOnInit(): void {
    this.loadInitialData();
  }

  loadInitialData(): void {
    this.loading = true;
    
    // Charger les médecins
    this.apiService.getMedecins().subscribe({
      next: (response: any) => {
        if (response.success) {
          this.medecins = response.data;
          console.log('👨‍⚕️ Médecins chargés:', this.medecins);
        } else {
          this.error = response.message || 'Erreur lors du chargement des médecins';
        }
        this.checkLoading();
      },
      error: (err: any) => {
        this.error = 'Erreur lors du chargement des médecins';
        this.loading = false;
        console.error('Error loading doctors:', err);
      }
    });

    // Utiliser l'utilisateur connecté comme patient
    const currentUser = this.authService.getCurrentUser();
    if (currentUser && currentUser.role === 'patient') {
      this.patients = [currentUser];
      this.formData.patient_id = currentUser.id.toString();
    } else {
      this.error = 'Vous devez être connecté en tant que patient pour prendre un rendez-vous';
    }
    
    this.checkLoading();
  }

  checkLoading(): void {
    setTimeout(() => {
      this.loading = false;
    }, 1000);
  }

  onSubmit(): void {
    this.loading = true;
    this.error = '';
    this.success = '';

    // Validation frontale
    if (!this.formData.medecin_id || !this.formData.date || !this.formData.heure_debut) {
      this.error = 'Veuillez remplir tous les champs obligatoires';
      this.loading = false;
      return;
    }

    // Formatage des heures
    const [heures, minutes] = this.formData.heure_debut.split(':').map(Number);
    const heureDebutFormatee = `${heures.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:00`;
    
    const heureFin = new Date(0, 0, 0, heures + 1, minutes);
    const heureFinFormatee = heureFin.toString().substring(16, 21) + ':00';

    // Préparation des données
    const rendezVousData = {
      patient_id: this.formData.patient_id,
      medecin_id: this.formData.medecin_id,
      date: this.formData.date,
      heure_debut: heureDebutFormatee,
      heure_fin: heureFinFormatee,
      mode_paiement: this.formData.mode_paiement,
      notes: this.formData.notes || '',
      statut: 'en_attente',
      statut_paiement: this.formData.mode_paiement === 'en_ligne' ? 'en_attente' : 'paye',
      montant: this.tarifConsultation
    };

    this.apiService.createRendezVous(rendezVousData).subscribe({
      next: (response: any) => {
        this.loading = false;
        if (response.success) {
          this.success = 'Rendez-vous créé avec succès !';
          this.newRendezVousId = response.data.id;
          
          if (this.formData.mode_paiement === 'en_ligne') {
            this.redirectToPayment(response.data.id, this.tarifConsultation);
          } else {
            setTimeout(() => {
              this.router.navigate(['/mes-rendezvous']);
            }, 1500);
          }
          
        } else {
          this.error = response.message;
        }
      },
      error: (err: any) => {
        this.loading = false;
        this.error = 'Erreur lors de la création du rendez-vous';
        console.error('Error creating appointment:', err);
      }
    });
  }

 // Dans prendre-rendezvous.component.ts
redirectToPayment(appointmentId: number, amount: number): void {
  this.router.navigate(['/paiement'], {
    queryParams: { 
      appointmentId: appointmentId,
      amount: amount 
    },
    state: {
      appointmentId: appointmentId,
      amount: amount
    }
  });
}

  resetForm(): void {
    const patientId = this.formData.patient_id;
    this.formData = {
      patient_id: patientId,
      medecin_id: '',
      date: '',
      heure_debut: '09:00',
      heure_fin: '10:00',
      mode_paiement: 'sur_place',
      notes: ''
    };
  }

  get selectedMedecin() {
    return this.medecins.find(m => m.id == this.formData.medecin_id);
  }

  get tarifConsultation() {
    return this.selectedMedecin ? this.selectedMedecin.tarif_consultation : 0;
  }

  getTodayDate(): string {
    return new Date().toISOString().split('T')[0];
  }

  // Méthode pour obtenir le nom du médecin sélectionné
  getSelectedDoctorName(): string {
    if (!this.formData.medecin_id) return '';
    
    const selectedDoctor = this.medecins.find(med => med.id == this.formData.medecin_id);
    
    if (selectedDoctor) {
      // Utilisez les propriétés corrigées du MedecinResource
      return `Dr. ${selectedDoctor.prenom} ${selectedDoctor.nom}`;
    }
    
    return '';
  }

  // Méthode pour obtenir la spécialité du médecin
  getSelectedDoctorSpecialty(): string {
    if (!this.formData.medecin_id) return '';
    
    const selectedDoctor = this.medecins.find(med => med.id == this.formData.medecin_id);
    
    if (selectedDoctor) {
      return selectedDoctor.specialite || 'Non spécifiée';
    }
    
    return '';
  }
}