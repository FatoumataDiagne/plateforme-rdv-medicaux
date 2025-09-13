import { Component, OnInit } from '@angular/core';
import { MedecinService, RendezVous } from '../../services/medecin.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-agenda',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './agenda.component.html',
  styleUrls: ['./agenda.component.css']
})
export class AgendaComponent implements OnInit {
  rendezVous: RendezVous[] = [];
  dateDebut: string = '';
  dateFin: string = '';
  chargement: boolean = false;
  erreur: string = '';

  constructor(private medecinService: MedecinService) {}

  ngOnInit(): void {
    // Définir une période par défaut (cette semaine)
    const aujourdHui = new Date();
    this.dateDebut = this.formatDate(aujourdHui);
    
    const dansUneSemaine = new Date();
    dansUneSemaine.setDate(aujourdHui.getDate() + 7);
    this.dateFin = this.formatDate(dansUneSemaine);
    
    this.chargerAgenda();
  }

  private formatDate(date: Date): string {
    return date.toISOString().split('T')[0];
  }

  chargerAgenda(): void {
    if (!this.dateDebut || !this.dateFin) {
      this.erreur = 'Veuillez sélectionner une période';
      return;
    }

    this.chargement = true;
    this.erreur = '';

    this.medecinService.getAgenda(this.dateDebut, this.dateFin).subscribe({
      next: (data) => {
        this.rendezVous = data;
        this.chargement = false;
      },
      error: (error) => {
        console.error('Erreur lors du chargement de l\'agenda', error);
        this.erreur = 'Impossible de charger l\'agenda';
        this.chargement = false;
      }
    });
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
        alert('Erreur lors de la génération du justificatif');
      }
    });
  }
}