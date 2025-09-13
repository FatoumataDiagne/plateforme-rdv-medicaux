import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminService, DashboardStats } from '../../services/admin.service';

@Component({
  selector: 'app-admin-statistiques',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './admin-statistiques.component.html',
  styleUrls: ['./admin-statistiques.component.css']
})
export class AdminStatistiquesComponent implements OnInit {
  stats: DashboardStats | null = null;
  isLoading = false;

  constructor(private adminService: AdminService) {}

  ngOnInit(): void {
    this.loadStats();
  }

  loadStats(): void {
    this.isLoading = true;
    this.adminService.getDashboardStats().subscribe({
      next: (response: any) => {
        this.stats = response.data || response;
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading stats:', error);
        this.isLoading = false;
      }
    });
  }
  // Méthode pour le graphique des justificatifs
getMaxMoisValue(parMois: any[]): number {
  if (!parMois || parMois.length === 0) return 1;
  
  const values = parMois.map(m => m.count || 0);
  const max = Math.max(...values);
  return max > 0 ? max : 1; // Éviter la division par zéro
}

  // Méthode helper pour formater les nombres
  formatNumber(num: number): string {
    return num?.toLocaleString('fr-FR') || '0';
  }

  // Méthode helper pour formater l'argent
  formatCurrency(amount: number): string {
    return amount?.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' }) || '0 €';
  }
   getPercentage(value: number, total: number): number {
    if (!total || total === 0 || !value) return 0;
    return (value / total) * 100;
  }
  // Méthode pour obtenir le top 3 des spécialités
getTopSpecialties(specialites: any[]): any[] {
  if (!specialites || specialites.length === 0) return [];
  
  // Trier par nombre de rendez-vous (décroissant) et prendre les 3 premiers
  return [...specialites]
    .sort((a, b) => (b.rendez_vous_count || 0) - (a.rendez_vous_count || 0))
    .slice(0, 3);
}

// Méthode pour obtenir la valeur maximale des rendez-vous
getMaxRdvValue(specialites: any[]): number {
  if (!specialites || specialites.length === 0) return 1;
  
  const values = specialites.map(s => s.rendez_vous_count || 0);
  const max = Math.max(...values);
  return max > 0 ? max : 1; // Éviter la division par zéro
}

// Méthode pour obtenir le total des rendez-vous
getTotalRendezVous(specialites: any[]): number {
  if (!specialites || specialites.length === 0) return 0;
  
  return specialites.reduce((total, spec) => total + (spec.rendez_vous_count || 0), 0);
}

// Méthode pour obtenir le total des revenus
getTotalRevenus(specialites: any[]): number {
  if (!specialites || specialites.length === 0) return 0;
  
  return specialites.reduce((total, spec) => total + (spec.revenus || 0), 0);
}

// Méthode pour obtenir la moyenne des rendez-vous par spécialité
getAverageRdvPerSpecialty(specialites: any[]): number {
  if (!specialites || specialites.length === 0) return 0;
  
  const total = this.getTotalRendezVous(specialites);
  return Math.round(total / specialites.length);
}
}
