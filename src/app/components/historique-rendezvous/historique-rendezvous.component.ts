import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-historique-rendezvous',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './historique-rendezvous.component.html',
  styleUrls: ['./historique-rendezvous.component.css']
})
export class HistoriqueRendezVousComponent implements OnInit {
  rendezVous: any[] = [];
  filteredRendezVous: any[] = [];
  loading = true;
  error = '';
  stats: any = {};

  // Filtres
  filters = {
    date_debut: '',
    date_fin: '',
    medecin_id: '',
    statut: '',
    specialite: '',
    search: ''
  };

  // Options de filtres
  statuts = ['tous', 'confirmé', 'en_attente', 'annulé', 'terminé'];
  specialites: string[] = [];
  medecins: any[] = [];

  constructor(
    private apiService: ApiService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.loadHistorique();
    this.loadStats();
  }

  loadHistorique(): void {
    this.loading = true;
    this.error = '';

    this.apiService.getRendezVous().subscribe({
      next: (response: any) => {
        this.loading = false;
        if (response.success) {
          this.rendezVous = response.data;
          this.filteredRendezVous = response.data;
          this.extractFilterOptions();
        } else {
          this.error = response.message;
        }
      },
      error: (err: any) => {
        this.loading = false;
        this.error = 'Erreur lors du chargement de l\'historique';
        console.error('Error loading history:', err);
      }
    });
  }

  loadStats(): void {
    this.apiService.getUserRendezVousStats().subscribe({
      next: (response: any) => {
        if (response.success) {
          this.stats = response.data;
        }
      },
      error: (err) => {
        console.error('Error loading stats:', err);
      }
    });
  }

  extractFilterOptions(): void {
    // Extraire les spécialités uniques
    this.specialites = [...new Set(this.rendezVous
      .filter(rdv => rdv.medecin?.specialite)
      .map(rdv => rdv.medecin.specialite))];

    // Extraire les médecins uniques
    this.medecins = this.rendezVous
      .filter((rdv, index, array) => 
        array.findIndex(r => r.medecin?.id === rdv.medecin?.id) === index
      )
      .map(rdv => rdv.medecin)
      .filter(medecin => medecin);
  }

  applyFilters(): void {
    this.filteredRendezVous = this.rendezVous.filter(rdv => {
      // Filtre par date
      if (this.filters.date_debut && new Date(rdv.date) < new Date(this.filters.date_debut)) {
        return false;
      }
      if (this.filters.date_fin && new Date(rdv.date) > new Date(this.filters.date_fin)) {
        return false;
      }

      // Filtre par médecin
      if (this.filters.medecin_id && rdv.medecin_id != this.filters.medecin_id) {
        return false;
      }

      // Filtre par statut
      if (this.filters.statut && this.filters.statut !== 'tous' && rdv.statut !== this.filters.statut) {
        return false;
      }

      // Filtre par spécialité
      if (this.filters.specialite && rdv.medecin?.specialite !== this.filters.specialite) {
        return false;
      }

      // Filtre par recherche
      if (this.filters.search) {
        const searchTerm = this.filters.search.toLowerCase();
        const matchesMedecin = rdv.medecin?.prenom?.toLowerCase().includes(searchTerm) ||
                              rdv.medecin?.nom?.toLowerCase().includes(searchTerm);
        const matchesSpecialite = rdv.medecin?.specialite?.toLowerCase().includes(searchTerm);
        const matchesDate = rdv.date.includes(searchTerm);
        
        if (!matchesMedecin && !matchesSpecialite && !matchesDate) {
          return false;
        }
      }

      return true;
    });
  }

  resetFilters(): void {
    this.filters = {
      date_debut: '',
      date_fin: '',
      medecin_id: '',
      statut: '',
      specialite: '',
      search: ''
    };
    this.applyFilters();
  }

  getStatusClass(statut: string): string {
    switch (statut) {
      case 'confirmé': return 'badge bg-success';
      case 'en_attente': return 'badge bg-warning';
      case 'annulé': return 'badge bg-danger';
      case 'terminé': return 'badge bg-secondary';
      default: return 'badge bg-info';
    }
  }

  canCancel(rdv: any): boolean {
    const rdvDate = new Date(rdv.date + 'T' + rdv.heure_debut);
    const now = new Date();
    const hoursDiff = (rdvDate.getTime() - now.getTime()) / (1000 * 60 * 60);
    
    return rdv.statut === 'confirmé' && hoursDiff > 24;
  }

  annulerRendezVous(rdvId: number): void {
    if (confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')) {
      this.apiService.updateRendezVous(rdvId, { statut: 'annulé' }).subscribe({
        next: (response: any) => {
          if (response.success) {
            this.loadHistorique(); // Recharger les données
          }
        },
        error: (err) => {
          this.error = 'Erreur lors de l\'annulation';
        }
      });
    }
  }
}