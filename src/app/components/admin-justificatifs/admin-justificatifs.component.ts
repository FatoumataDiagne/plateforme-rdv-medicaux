import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';

@Component({
  selector: 'app-admin-justificatifs',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-justificatifs.component.html',
  styleUrls: ['./admin-justificatifs.component.css']
})
export class AdminJustificatifsComponent implements OnInit {
  justificatifs: any[] = [];
  isLoading = false;
  currentPage = 1;
  itemsPerPage = 10;
  totalPages = 1;
  totalItems = 0;
  stats: any = {};
  error = '';
  searchText = '';

  constructor(private apiService: ApiService) {}

  ngOnInit(): void {
    this.loadJustificatifs();
    this.loadStats();
  }

  loadJustificatifs(): void {
    this.isLoading = true;
    this.error = '';
    
    this.apiService.getJustificatifsAdmin(this.currentPage, this.itemsPerPage, this.searchText).subscribe({
      next: (response: any) => {
        if (response && response.success) {
          this.justificatifs = response.data || [];
          this.totalItems = response.meta?.total || 0;
          this.totalPages = response.meta?.last_page || 1;
        } else {
          this.error = response?.message || 'Erreur de récupération';
        }
        this.isLoading = false;
      },
      error: (err) => {
        this.error = 'Erreur lors du chargement des justificatifs';
        this.isLoading = false;
      }
    });
  }

  loadStats(): void {
    this.apiService.getStatistiquesJustificatifs().subscribe({
      next: (response: any) => {
        if (response && response.success) {
          this.stats = response.data || {};
        }
      },
      error: (err) => {
        console.error('Erreur statistiques:', err);
      }
    });
  }

  downloadJustificatif(id: number, filename: string): void {
    this.apiService.downloadJustificatifAdmin(id).subscribe({
      next: (blob: Blob) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename || `justificatif-${id}.pdf`;
        a.click();
        window.URL.revokeObjectURL(url);
      },
      error: (error) => {
        this.error = 'Erreur lors du téléchargement';
      }
    });
  }

  changePage(page: number): void {
    if (page >= 1 && page <= this.totalPages) {
      this.currentPage = page;
      this.loadJustificatifs();
    }
  }

  searchJustificatifs(): void {
    this.currentPage = 1;
    this.loadJustificatifs();
  }

  // Méthodes utilitaires
  getPages(): number[] {
    const pages: number[] = [];
    const maxVisiblePages = 5;
    let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1);
    
    for (let i = startPage; i <= endPage; i++) {
      pages.push(i);
    }
    
    return pages;
  }

  min(a: number, b: number): number {
    return Math.min(a, b);
  }
}