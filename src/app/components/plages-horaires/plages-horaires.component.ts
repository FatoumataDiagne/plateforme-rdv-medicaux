// src/app/medecin/plages-horaires/plages-horaires.component.ts
import { Component, OnInit } from '@angular/core';
import { MedecinService, PlageHoraire } from '../../services/medecin.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-plages-horaires',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './plages-horaires.component.html',
  styleUrls: ['./plages-horaires.component.css']
})
export class PlagesHorairesComponent implements OnInit {
  plagesHoraires: PlageHoraire[] = [];
  nouvellePlage: PlageHoraire = {
    jour_semaine: 'Lundi',
    heure_debut: '09:00',
    heure_fin: '17:00',
    medecin_id: 0 // Sera rempli automatiquement côté backend
  };
  joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
  message: string = '';
  erreur: string = '';

  constructor(private medecinService: MedecinService) {}

  ngOnInit(): void {
    this.chargerPlagesHoraires();
  }

  chargerPlagesHoraires(): void {
    this.medecinService.getPlagesHoraires().subscribe({
      next: (data) => {
        this.plagesHoraires = data;
      },
      error: (error) => {
        console.error('Erreur lors du chargement des plages horaires', error);
        this.erreur = 'Impossible de charger les plages horaires';
      }
    });
  }

  ajouterPlageHoraire(): void {
    this.medecinService.addPlageHoraire(this.nouvellePlage).subscribe({
      next: (plage) => {
        this.plagesHoraires.push(plage);
        this.nouvellePlage = {
          jour_semaine: 'Lundi',
          heure_debut: '09:00',
          heure_fin: '17:00',
          medecin_id: 0
        };
        this.message = 'Plage horaire ajoutée avec succès';
        this.erreur = '';
        setTimeout(() => this.message = '', 3000);
      },
      error: (error) => {
        console.error('Erreur lors de l\'ajout de la plage horaire', error);
        this.erreur = 'Erreur lors de l\'ajout de la plage horaire';
        this.message = '';
      }
    });
  }

  supprimerPlageHoraire(id: number): void {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette plage horaire ?')) {
      this.medecinService.deletePlageHoraire(id).subscribe({
        next: () => {
          this.plagesHoraires = this.plagesHoraires.filter(p => p.id !== id);
          this.message = 'Plage horaire supprimée avec succès';
          this.erreur = '';
          setTimeout(() => this.message = '', 3000);
        },
        error: (error) => {
          console.error('Erreur lors de la suppression de la plage horaire', error);
          this.erreur = 'Erreur lors de la suppression de la plage horaire';
          this.message = '';
        }
      });
    }
  }
}