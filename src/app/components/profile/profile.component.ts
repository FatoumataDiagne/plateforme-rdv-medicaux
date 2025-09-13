import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, FormsModule], // ✅ FormsModule pour template-driven forms
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.css']
})
export class ProfileComponent implements OnInit {
  user: any = {};
  loading = false;
  error = '';
  success = '';

  constructor(private authService: AuthService) {}

  ngOnInit(): void {
    this.loadUserProfile();
  }

  loadUserProfile(): void {
    const currentUser = this.authService.getCurrentUser();
    if (currentUser) {
      this.user = { ...currentUser };
    }
  }

  onSubmit(): void {
    this.loading = true;
    this.error = '';
    this.success = '';

    this.authService.updateProfile(this.user).subscribe({
      next: (response: any) => { // ✅ Ajoute le type 'any'
        this.loading = false;
        
        if (response.success) {
          this.success = 'Profil mis à jour avec succès !';
          // Met à jour le localStorage
          localStorage.setItem('current_user', JSON.stringify(response.data.user));
          // Force le refresh du subject
          window.location.reload(); // Solution simple pour rafraîchir
        } else {
          this.error = response.message || 'Erreur lors de la mise à jour';
        }
      },
      error: (err: any) => { // ✅ Ajoute le type 'any'
        this.loading = false;
        this.error = 'Erreur lors de la mise à jour du profil';
        
        if (err.error && err.error.errors) {
          this.error = Object.values(err.error.errors).join(', ');
        } else if (err.error && err.error.message) {
          this.error = err.error.message;
        }
        
        console.error('Update profile error:', err);
      }
    });
  }
}