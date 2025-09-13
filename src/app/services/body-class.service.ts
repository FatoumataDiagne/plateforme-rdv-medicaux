import { Injectable } from '@angular/core';
import { AuthService } from './auth.service';

@Injectable({
  providedIn: 'root'
})
export class BodyClassService {
  constructor(private authService: AuthService) {}

  updateBodyClass(): void {
    const body = document.body;
    
    // Supprimer les anciennes classes de rôle
    body.classList.remove('patient', 'medecin', 'admin', 'logged-in', 'logged-out');
    
    // Ajouter les nouvelles classes
    if (this.authService.isLoggedIn()) {
      body.classList.add('logged-in');
      const user = this.authService.getCurrentUser();
      if (user) {
        body.classList.add(user.role);
      }
    } else {
      body.classList.add('logged-out');
    }
  }
}