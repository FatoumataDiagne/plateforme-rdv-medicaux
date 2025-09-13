
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { RouterModule } from '@angular/router';
import { AdminService, DashboardStats } from '../../services/admin.service';
import { StatsCardComponent } from '../../components/stats-card/stats-card.component';
import { BarChartComponent } from '../../components/bar-chart/bar-chart.component';
import { PieChartComponent } from '../../components/pie-chart/pie-chart.component';
import { MatIconModule } from '@angular/material/icon';


@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  imports: [
    CommonModule, 
    HttpClientModule, 
    RouterModule, 
    StatsCardComponent,
    PieChartComponent,
    BarChartComponent,
    MatIconModule
  ],
   templateUrl: './admin-dashboard.component.html',  // ← Change le nom
  styleUrls: ['./admin-dashboard.component.css']
})
export class AdminDashboardComponent implements OnInit {
  stats!: DashboardStats;
  loading = true;
  error: string | null = null;

  // Données pour les graphiques
  rdvStatusData: { label: string; value: number }[] = [];
  specialiteData: { label: string; value: number }[] = [];
  paiementStatusData: { label: string; value: number }[] = [];

  constructor(private adminService: AdminService) {}

  ngOnInit(): void {
    this.loadStats();
  }
// Dans ta méthode loadStats(), améliore la gestion d'erreurs
loadStats(): void {
  this.loading = true;
  this.error = null;

  this.adminService.getDashboardStats().subscribe({
    next: (response) => {
      if (response.success) {
        this.stats = response.data;
        this.prepareChartData();
      } else {
        this.error = 'Erreur lors du chargement des statistiques';
      }
      this.loading = false;
    },
    error: (err) => {
      if (err.status === 401) {
        this.error = 'Session expirée. Veuillez vous reconnecter.';
      } else if (err.status === 403) {
        this.error = 'Accès non autorisé. Droits administrateur requis.';
      } else {
        this.error = 'Erreur de connexion au serveur';
      }
      this.loading = false;
      console.error('Erreur dashboard:', err);
    }
  });
}

  private prepareChartData(): void {
    // Données pour le graphique des statuts de RDV
    this.rdvStatusData = [
      { label: 'Confirmés', value: this.stats.par_statut.rendezvous.confirme },
      { label: 'En attente', value: this.stats.par_statut.rendezvous.en_attente },
      { label: 'Annulés', value: this.stats.par_statut.rendezvous.annule }
    ];

    // Données pour le graphique des statuts de paiement
    this.paiementStatusData = [
      { label: 'Payés', value: this.stats.par_statut.paiements.paye },
      { label: 'En attente', value: this.stats.par_statut.paiements.en_attente },
      { label: 'Remboursés', value: this.stats.par_statut.paiements.rembourse }
    ];

    // Données pour le graphique des spécialités (top 5)
    this.specialiteData = this.stats.advanced.specialites
      .sort((a, b) => b.rendez_vous_count - a.rendez_vous_count)
      .slice(0, 5)
      .map(spec => ({
        label: spec.name,
        value: spec.rendez_vous_count
      }));
  }

  refreshStats(): void {
    this.loadStats();
  }
  // Chargement périodique des stats
startAutoRefresh(): void {
  setInterval(() => {
    this.loadStats();
  }, 30000); // Actualise toutes les 30 secondes
}

// Export des données
exportStats(): void {
  const dataStr = JSON.stringify(this.stats, null, 2);
  const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
  const exportFileDefaultName = 'stats-dashboard.json';
  
  const linkElement = document.createElement('a');
  linkElement.setAttribute('href', dataUri);
  linkElement.setAttribute('download', exportFileDefaultName);
  linkElement.click();
}

// Filtrage par date
filterByDate(startDate: string, endDate: string): void {
  // Implémente le filtrage par date
  console.log('Filtrage du', startDate, 'au', endDate);
}
}