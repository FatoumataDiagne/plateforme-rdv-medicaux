import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { AIChatComponent } from '../ai-chat/ai-chat.component'; // ← Import ajouté

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive, AIChatComponent], // ← AIChatComponent ajouté
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent {
  isMenuOpen = false;
  pendingAppointmentsCount = 0;
  showNotifications = false;
notifications: any[] = [];
unreadNotificationsCount = 0;

  constructor(
    public authService: AuthService,
    private router: Router
  ) { }

  // Méthode pour ouvrir le chat IA
  openAIChat(): void {
    // Cette méthode sera implémentée pour contrôler le composant AI
    console.log('Ouvrir le chat IA');
    
    // Fermer le menu mobile si ouvert
    this.isMenuOpen = false;
  }

  getRoleLabel(role: string | undefined): string {
    switch (role) {
      case 'patient': return 'Patient';
      case 'medecin': return 'Médecin';
      case 'admin': return 'Administrateur';
      default: return 'Utilisateur';
    }
  }

  toggleMenu(): void {
    this.isMenuOpen = !this.isMenuOpen;
  }

  logout(): void {
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigate(['/']);
        this.isMenuOpen = false;
      },
      error: (err) => {
        console.error('Erreur lors de la déconnexion:', err);
        localStorage.removeItem('auth_token');
        localStorage.removeItem('current_user');
        this.router.navigate(['/']);
        this.isMenuOpen = false;
      }
    });
  }
  
}