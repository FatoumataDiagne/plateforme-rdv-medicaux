import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../services/admin.service';

@Component({
  selector: 'app-admin-specialites',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-specialites.component.html',
  styleUrls: ['./admin-specialites.component.css']
})
export class AdminSpecialitesComponent implements OnInit {
  specialites: any[] = [];
  filteredSpecialites: any[] = [];
  displayedSpecialites: any[] = [];
  newSpecialite = { nom: '', description: '' };
  editingSpecialite: any = null;
  isLoading = false;
  errorMessage = '';
  
  // Propriétés pour la pagination
  currentPage = 1;
  itemsPerPage = 10;
  totalPages = 1;
  pages: number[] = [];
  searchText = '';

  constructor(private adminService: AdminService) {}

  ngOnInit(): void {
    this.loadSpecialites();
  }

  loadSpecialites(): void {
    this.isLoading = true;
    this.adminService.getSpecialites().subscribe({
      next: (response: any) => {
        this.specialites = response.data || response;
        this.filteredSpecialites = [...this.specialites];
        this.updatePagination();
        this.isLoading = false;
      },
      error: (error) => {
        this.errorMessage = 'Erreur lors du chargement des spécialités';
        this.isLoading = false;
        console.error('Error loading specialites:', error);
      }
    });
  }

  createSpecialite(): void {
    if (!this.newSpecialite.nom.trim()) {
      this.errorMessage = 'Le nom de la spécialité est obligatoire';
      return;
    }

    this.adminService.createSpecialite(this.newSpecialite).subscribe({
      next: (response: any) => {
        const newSpec = response.data || response;
        this.specialites.push(newSpec);
        this.filteredSpecialites.push(newSpec);
        this.updatePagination();
        this.newSpecialite = { nom: '', description: '' };
        this.errorMessage = '';
      },
      error: (error) => {
        this.errorMessage = 'Erreur lors de la création';
        console.error('Error creating specialite:', error);
      }
    });
  }

  startEdit(specialite: any): void {
    this.editingSpecialite = { ...specialite };
  }

  updateSpecialite(): void {
    if (!this.editingSpecialite.nom.trim()) {
      this.errorMessage = 'Le nom de la spécialité est obligatoire';
      return;
    }

    this.adminService.updateSpecialite(this.editingSpecialite.id, this.editingSpecialite).subscribe({
      next: (response: any) => {
        const updatedSpec = response.data || response;
        const index = this.specialites.findIndex(s => s.id === this.editingSpecialite.id);
        if (index !== -1) {
          this.specialites[index] = updatedSpec;
        }
        
        const filteredIndex = this.filteredSpecialites.findIndex(s => s.id === this.editingSpecialite.id);
        if (filteredIndex !== -1) {
          this.filteredSpecialites[filteredIndex] = updatedSpec;
        }
        
        this.updatePagination();
        this.cancelEdit();
        this.errorMessage = '';
      },
      error: (error) => {
        this.errorMessage = 'Erreur lors de la modification';
        console.error('Error updating specialite:', error);
      }
    });
  }

  deleteSpecialite(id: number): void {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette spécialité ?')) {
      this.adminService.deleteSpecialite(id).subscribe({
        next: () => {
          this.specialites = this.specialites.filter(s => s.id !== id);
          this.filteredSpecialites = this.filteredSpecialites.filter(s => s.id !== id);
          this.updatePagination();
        },
        error: (error) => {
          this.errorMessage = 'Erreur lors de la suppression';
          console.error('Error deleting specialite:', error);
        }
      });
    }
  }

  cancelEdit(): void {
    this.editingSpecialite = null;
  }

  // Méthodes pour la pagination et la recherche
  filterSpecialites(): void {
    if (!this.searchText) {
      this.filteredSpecialites = [...this.specialites];
    } else {
      const searchLower = this.searchText.toLowerCase();
      this.filteredSpecialites = this.specialites.filter(s => 
        s.nom.toLowerCase().includes(searchLower) || 
        (s.description && s.description.toLowerCase().includes(searchLower))
      );
    }
    this.currentPage = 1;
    this.updatePagination();
  }

  updatePagination(): void {
    // Calculer le nombre total de pages
    this.totalPages = Math.ceil(this.filteredSpecialites.length / this.itemsPerPage);
    
    // S'assurer que la page actuelle est valide
    if (this.currentPage > this.totalPages) {
      this.currentPage = this.totalPages || 1;
    }
    
    // Générer la liste des pages à afficher
    this.pages = [];
    for (let i = 1; i <= this.totalPages; i++) {
      this.pages.push(i);
    }
    
    // Mettre à jour les éléments affichés
    const startIndex = (this.currentPage - 1) * this.itemsPerPage;
    const endIndex = startIndex + this.itemsPerPage;
    this.displayedSpecialites = this.filteredSpecialites.slice(startIndex, endIndex);
  }

  setPage(page: number): void {
    if (page >= 1 && page <= this.totalPages) {
      this.currentPage = page;
      this.updatePagination();
    }
  }

  // Getters pour l'affichage des informations de pagination
  get startIndex(): number {
    return (this.currentPage - 1) * this.itemsPerPage;
  }

  get endIndex(): number {
    return Math.min(this.startIndex + this.itemsPerPage, this.filteredSpecialites.length);
  }
}