import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';

@Component({
  selector: 'app-admin-users',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-users.component.html',
  styleUrls: ['./admin-users.component.css']
})
export class AdminUsersComponent implements OnInit {
  users: any[] = [];
  loading = true;
  error = '';
  success = '';
  currentPage = 1;
  perPage = 10;
  totalPages = 0;
  totalItems = 0;
  searchTerm = '';
  editingUser: any = null;
  
  // Pour la pagination avancée
  showStartEllipsis = false;
  showEndEllipsis = false;

  constructor(private apiService: ApiService) {}

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.loading = true;
    this.error = '';

    this.apiService.getAllUsers(this.currentPage, this.perPage, this.searchTerm).subscribe({
      next: (response) => {
        if (response.success && response.data) {
          this.users = response.data;
          this.currentPage = response.meta?.current_page || 1;
          this.totalPages = response.meta?.last_page || 1;
          this.totalItems = response.meta?.total || 0;
        } else {
          this.users = [];
          this.error = response.message || 'Aucune donnée reçue';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Erreur lors du chargement des utilisateurs';
        this.loading = false;
        console.error('Error loading users:', err);
      }
    });
  }

  onSearch(): void {
    this.currentPage = 1;
    this.loadUsers();
  }

  changePage(page: number): void {
    if (page >= 1 && page <= this.totalPages) {
      this.currentPage = page;
      this.loadUsers();
    }
  }

  onPageSizeChange(): void {
    this.currentPage = 1;
    this.loadUsers();
  }

  // Méthode pour générer les numéros de page à afficher
  getPages(): number[] {
    const pages: number[] = [];
    const maxVisiblePages = 5;
    
    let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    this.showStartEllipsis = startPage > 1;
    this.showEndEllipsis = endPage < this.totalPages;
    
    for (let i = startPage; i <= endPage; i++) {
      pages.push(i);
    }
    
    return pages;
  }

  getRoleText(role: string): string {
    switch (role) {
      case 'admin': return 'Administrateur';
      case 'medecin': return 'Médecin';
      case 'patient': return 'Patient';
      default: return role;
    }
  }

  startEdit(user: any): void {
    this.editingUser = { ...user };
  }

  cancelEdit(): void {
    this.editingUser = null;
  }

  updateUser(): void {
    if (!this.editingUser) return;

    this.apiService.updateUser(this.editingUser.id, this.editingUser).subscribe({
      next: (response: any) => {
        const index = this.users.findIndex(u => u.id === this.editingUser.id);
        if (index !== -1) {
          this.users[index] = response.data || response;
        }
        this.cancelEdit();
        this.success = 'Utilisateur mis à jour avec succès';
      },
      error: (error) => {
        this.error = 'Erreur lors de la modification';
        console.error('Error updating user:', error);
      }
    });
  }

  deleteUser(id: number): void {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
      this.apiService.deleteUser(id).subscribe({
        next: () => {
          this.users = this.users.filter(u => u.id !== id);
          this.success = 'Utilisateur supprimé avec succès';
          // Recharger les données pour mettre à jour la pagination
          this.loadUsers();
        },
        error: (error) => {
          this.error = 'Erreur lors de la suppression';
          console.error('Error deleting user:', error);
        }
      });
    }
  }

  // Fonction utilitaire pour Math.min dans le template
  Math = Math;
}