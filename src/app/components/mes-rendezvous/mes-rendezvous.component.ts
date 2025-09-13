import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-mes-rendezvous',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './mes-rendezvous.component.html',
  styleUrls: ['./mes-rendezvous.component.css']
})
export class MesRendezVousComponent implements OnInit {
  rendezVous: any[] = [];
  filteredRendezVous: any[] = [];
  loading = true;
  error = '';
  success = '';
  debugMode = false; // Ajout de la propriété debugMode manquante

  // Système d'onglets et filtres
  activeTab: 'a_venir' | 'historique' | 'annules' = 'a_venir';
  filters = {
    search: '',
    statut: 'tous'
  };

  constructor(
    private apiService: ApiService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    console.log('🔐 Current token:', this.authService.getToken());
    console.log('👤 Current user:', this.authService.getCurrentUser());
    this.loadMesRendezVous();
  }

  // Ajout de la méthode toggleDebug manquante
  toggleDebug(): void {
    this.debugMode = !this.debugMode;
  }

  // Ajout de la méthode isConfirmed manquante
  isConfirmed(rdv: any): boolean {
    if (!rdv || !rdv.statut) return false;
    
    const statut = rdv.statut.toString().toLowerCase().trim();
    return statut === 'confirmé' || 
           statut === 'confirme' || 
           statut === 'confirmed' ||
           statut === 'valide' ||
           statut === 'validé';
  }

  loadMesRendezVous(): void {
    this.loading = true;
    this.error = '';
    
    this.apiService.getRendezVous().subscribe({
      next: (response: any) => {
        console.log('🔍 Réponse complète:', response);
        
        if (response && response.success) {
          const userId = this.authService.getCurrentUser()?.id;
          console.log('👤 User ID:', userId);
          
          // Filtrage par utilisateur connecté
          this.rendezVous = response.data.filter((rdv: any) => {
            return rdv.patient_id == userId;
          });
          
          console.log('✅ Rendez-vous filtrés:', this.rendezVous);
          
          // DEBUG: Afficher tous les statuts pour vérification
          this.rendezVous.forEach(rdv => {
            console.log(`RDV ${rdv.id}: Statut = "${rdv.statut}", Type = ${typeof rdv.statut}`);
          });
          
          this.applyFiltersAndTabs();
        } else {
          this.error = response.message || 'Format de réponse inattendu';
        }
        this.loading = false;
      },
      error: (err) => {
        console.error('❌ Erreur détaillée:', err);
        this.error = 'Erreur lors du chargement des rendez-vous';
        this.loading = false;
      }
    });
  }

  // Vérifier si le rendez-vous est dans le futur
  isFutureAppointment(rdv: any): boolean {
    if (!rdv || !rdv.date || !rdv.heure_debut) return false;
    
    const now = new Date();
    const appointmentDate = new Date(rdv.date + 'T' + rdv.heure_debut);
    return appointmentDate >= now;
  }

  // Filtrage par onglet et recherche
  applyFiltersAndTabs(): void {
    let filtered = this.rendezVous;

    // Filtre par onglet actif
    const now = new Date();
    switch (this.activeTab) {
      case 'a_venir':
        filtered = filtered.filter(rdv => 
          new Date(rdv.date + 'T' + rdv.heure_debut) >= now && 
          rdv.statut !== 'annulé'
        );
        break;
      case 'historique':
        filtered = filtered.filter(rdv => 
          new Date(rdv.date + 'T' + rdv.heure_debut) < now
        );
        break;
      case 'annules':
        filtered = filtered.filter(rdv => 
          rdv.statut === 'annulé' || rdv.statut === 'annule'
        );
        break;
    }

    // Filtre de recherche
    if (this.filters.search) {
      const searchTerm = this.filters.search.toLowerCase();
      filtered = filtered.filter(rdv =>
        (rdv.medecin_nom && rdv.medecin_nom.toLowerCase().includes(searchTerm)) ||
        (rdv.medecin_specialite && rdv.medecin_specialite.toLowerCase().includes(searchTerm)) ||
        (rdv.date && rdv.date.includes(searchTerm))
      );
    }

    // Filtre par statut (si différent de "tous")
    if (this.filters.statut !== 'tous') {
      filtered = filtered.filter(rdv => {
        if (this.filters.statut === 'confirmé') {
          return this.isConfirmed(rdv);
        } else if (this.filters.statut === 'en_attente') {
          const statut = rdv.statut.toString().toLowerCase().trim();
          return statut === 'en_attente' || statut === 'en attente' || statut === 'pending';
        } else if (this.filters.statut === 'annulé') {
          const statut = rdv.statut.toString().toLowerCase().trim();
          return statut === 'annulé' || statut === 'annule' || statut === 'cancelled';
        }
        return true;
      });
    }

    this.filteredRendezVous = filtered;
  }

  // Changement d'onglet
  setActiveTab(tab: 'a_venir' | 'historique' | 'annules'): void {
    this.activeTab = tab;
    this.applyFiltersAndTabs();
  }

  // Application des filtres
  onFilterChange(): void {
    this.applyFiltersAndTabs();
  }

  annulerRendezVous(id: number): void {
    if (confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')) {
      this.apiService.deleteRendezVous(id).subscribe({
        next: (response: any) => {
          if (response.success) {
            this.success = 'Rendez-vous annulé avec succès';
            this.loadMesRendezVous();
          } else {
            this.error = response.message;
          }
        },
        error: (err) => {
          this.error = 'Erreur lors de l\'annulation du rendez-vous';
          console.error('Error deleting appointment:', err);
        }
      });
    }
  }

  getStatusBadgeClass(statut: string): string {
    if (!statut) return 'badge bg-secondary';
    
    const statutLower = statut.toString().toLowerCase().trim();
    
    if (this.isConfirmed({statut: statutLower})) return 'badge bg-success';
    if (statutLower === 'en_attente' || statutLower === 'en attente' || statutLower === 'pending') return 'badge bg-warning';
    if (statutLower === 'annulé' || statutLower === 'annule' || statutLower === 'cancelled') return 'badge bg-danger';
    
    return 'badge bg-secondary';
  }

  getStatusText(statut: string): string {
    if (!statut) return 'Inconnu';
    
    const statutLower = statut.toString().toLowerCase().trim();
    
    if (this.isConfirmed({statut: statutLower})) return 'Confirmé';
    if (statutLower === 'en_attente' || statutLower === 'en attente' || statutLower === 'pending') return 'En attente';
    if (statutLower === 'annulé' || statutLower === 'annule' || statutLower === 'cancelled') return 'Annulé';
    
    return statut;
  }

  downloadJustificatif(id: number): void {
    this.apiService.generatePdf(id).subscribe({
      next: (res: Blob) => {
        const fileURL = window.URL.createObjectURL(res);
        const a = document.createElement('a');
        a.href = fileURL;
        a.download = `justificatif-${id}.pdf`;
        a.click();
        this.success = 'PDF téléchargé avec succès ✅';
      },
      error: (err) => {
        this.error = 'Erreur lors de la génération du PDF ❌';
        console.error('Error generating PDF:', err);
      }
    });
  }
}