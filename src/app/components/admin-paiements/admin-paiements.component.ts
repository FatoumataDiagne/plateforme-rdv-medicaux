import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminService } from '../../services/admin.service';
import { catchError } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
  selector: 'app-admin-paiements',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './admin-paiements.component.html',
  styleUrls: ['./admin-paiements.component.css']
})
export class AdminPaiementsComponent implements OnInit {
  payments: any[] = [];
  currentPage = 1;
  itemsPerPage = 10;
  totalItems = 0;
  isLoading = false;
  errorMessage = '';

  constructor(private adminService: AdminService) {}

  ngOnInit(): void {
    this.loadPayments();
  }

  loadPayments(): void {
    this.isLoading = true;
    this.errorMessage = '';
    
    this.adminService.getPaiements(this.currentPage, this.itemsPerPage)
      .pipe(
        catchError(error => {
          this.errorMessage = error.message || 'Erreur serveur. Veuillez réessayer.';
          this.isLoading = false;
          console.error('Erreur API:', error);
          return of(null);
        })
      )
      .subscribe({
        next: (response: any) => {
          if (response && response.success) {
            this.payments = response.data || [];
            this.totalItems = response.pagination?.total || 0;
          } else {
            this.errorMessage = response?.message || 'Réponse inattendue du serveur';
          }
          this.isLoading = false;
        },
        error: (error) => {
          this.errorMessage = 'Erreur de connexion au serveur';
          this.isLoading = false;
          console.error('Erreur:', error);
        }
      });
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadPayments();
  }

  get totalPages(): number {
    return Math.ceil(this.totalItems / this.itemsPerPage);
  }

  getStatusBadgeClass(status: string): string {
    switch (status?.toLowerCase()) {
      case 'payé':
      case 'success':
        return 'badge bg-success';
      case 'en_attente':
      case 'pending':
        return 'badge bg-warning';
      case 'annulé':
      case 'failed':
        return 'badge bg-danger';
      case 'remboursé':
        return 'badge bg-info';
      default:
        return 'badge bg-secondary';
    }
  }

  // Méthode pour réessayer
  retry(): void {
    this.loadPayments();
  }
}