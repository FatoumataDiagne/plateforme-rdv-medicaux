import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';
import { CommonModule } from '@angular/common'; // ← Import important
import { HttpClientModule } from '@angular/common/http'; // ← Pour les requêtes HTTP

@Component({
  selector: 'app-specialite-list',
  standalone: true, // ← Doit être true pour Angular 17+
  imports: [CommonModule], // ← Importez CommonModule pour *ngFor, *ngIf
  templateUrl: './specialite-list.component.html',
  styleUrls: ['./specialite-list.component.css']
})
export class SpecialiteListComponent implements OnInit {
  specialites: any[] = [];
  loading: boolean = true;
  error: string = '';

  constructor(private apiService: ApiService) { }

  ngOnInit(): void {
    this.loadSpecialites();
  }

  loadSpecialites(): void {
    this.loading = true;
    this.apiService.getSpecialites().subscribe({
      next: (response) => {
        if (response.success) {
          this.specialites = response.data;
        } else {
          this.error = response.message;
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Erreur lors du chargement des spécialités';
        this.loading = false;
        console.error('Error loading specialites:', err);
      }
    });
  }
}