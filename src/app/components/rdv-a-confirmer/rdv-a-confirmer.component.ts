// src/app/components/rdv-a-confirmer/rdv-a-confirmer.component.ts
import { Component, OnInit } from '@angular/core';
import { MedecinService, RendezVous } from '../../services/medecin.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-rdv-a-confirmer',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './rdv-a-confirmer.component.html',
  styleUrls: ['./rdv-a-confirmer.component.css']
})
export class RdvAConfirmerComponent implements OnInit {
  rendezVous: RendezVous[] = [];
  message: string = '';
  erreur: string = '';
  motifRefus: string = '';
  selectedRdvId: number | null = null; 
  showRefusModal: boolean = false;
currentRdvId: number | null = null;

  constructor(private medecinService: MedecinService) {}

  ngOnInit(): void {
    this.chargerRendezVous();
  }

   chargerRendezVous(): void {
    this.medecinService.getRendezVousAConfirmer().subscribe({
      next: (data) => {
        console.log('Données reçues:', data); // Debug
        this.rendezVous = data;
      },
      error: (error) => {
        console.error('Erreur lors du chargement des rendez-vous', error);
        this.erreur = 'Impossible de charger les rendez-vous';
      }
    });
  }
  confirmerRendezVous(id: number): void {
    this.medecinService.confirmerRendezVous(id).subscribe({
      next: () => {
        this.message = 'Rendez-vous confirmé avec succès';
        this.erreur = '';
        this.chargerRendezVous();
        setTimeout(() => this.message = '', 3000);
      },
      error: (error) => {
        console.error('Erreur lors de la confirmation du rendez-vous', error);
        this.erreur = 'Erreur lors de la confirmation du rendez-vous';
        this.message = '';
      }
    });
  }

 ouvrirModalRefus(rdvId: number): void {
  this.currentRdvId = rdvId;
  this.showRefusModal = true;
  this.motifRefus = ''; // Réinitialiser le motif
}

// Méthode pour fermer le modal
closeModal(): void {
  this.showRefusModal = false;
  this.currentRdvId = null;
}

  genererJustificatif(id: number): void {
    this.medecinService.genererJustificatif(id).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `justificatif-rdv-${id}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      },
      error: (error) => {
        console.error('Erreur lors de la génération du justificatif', error);
        this.erreur = 'Erreur lors de la génération du justificatif';
      }
    });
  }
  refuserRendezVous(): void {
  if (this.currentRdvId && this.motifRefus) {
    // Appeler votre service pour refuser le rendez-vous
    this.medecinService.refuserRendezVous(this.currentRdvId, this.motifRefus).subscribe({
      next: () => {
        this.message = 'Rendez-vous refusé avec succès';
        this.chargerRendezVous(); // Recharger la liste
        this.closeModal();
      },
      error: (error) => {
        this.erreur = 'Erreur lors du refus du rendez-vous';
        console.error(error);
      }
    });
  } else {
    this.erreur = 'Veuillez saisir un motif de refus';
  }
}
}