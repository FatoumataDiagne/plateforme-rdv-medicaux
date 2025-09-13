import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { FormsModule } from '@angular/forms'; 

@Component({
  selector: 'app-medecin-list',
  standalone: true,
  imports: [CommonModule, FormsModule], 
  templateUrl: './medecin-list.component.html',
  styleUrls: ['./medecin-list.component.css']
})
export class MedecinListComponent implements OnInit {
  medecins: any[] = [];
  loading: boolean = true;
  error: string = '';
  searchTerm: string = '';
  selectedSpecialite: string = '';

  constructor(private apiService: ApiService) { }

  ngOnInit(): void {
    this.loadMedecins();
  }

  loadMedecins(): void {
    this.loading = true;
    this.apiService.getMedecins().subscribe({
      next: (response) => {
        if (response.success) {
          this.medecins = response.data;
        } else {
          this.error = response.message;
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Erreur lors du chargement des médecins';
        this.loading = false;
        console.error('Error loading medecins:', err);
      }
    });
  }

  // Filtrer les médecins par recherche et spécialité
  get filteredMedecins(): any[] {
    return this.medecins.filter(medecin => {
      const matchesSearch = medecin.prenom.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                           medecin.nom.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                           medecin.specialite.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                           medecin.ville.toLowerCase().includes(this.searchTerm.toLowerCase());
      
      const matchesSpecialite = this.selectedSpecialite === '' || 
                               medecin.specialite === this.selectedSpecialite;
      
      return matchesSearch && matchesSpecialite;
    });
  }

  // Obtenir la liste des spécialités uniques pour le filtre
  get specialites(): string[] {
    return [...new Set(this.medecins.map(medecin => medecin.specialite))].sort();
  }
}