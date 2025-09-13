import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, FormsModule], // ✅ FormsModule pour template-driven forms
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent {
  ageError(ageError: any) {
    throw new Error('Method not implemented.');
  }
  checkAge() {
    throw new Error('Method not implemented.');
  }
  userData = {
    prenom: '',
    nom: '',
    email: '',
    password: '',
    password_confirmation: '',
    telephone: '',
    role: 'patient',
    date_naissance: '',
    adresse: ''
  };
  
  loading = false;
  error = '';
  success = '';

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  onSubmit() {
    this.loading = true;
    this.error = '';
    this.success = '';

    this.authService.register(this.userData).subscribe({
      next: (response: any) => { // ✅ Ajoute le type 'any'
        this.loading = false;
        
        if (response.success) {
          this.success = 'Compte créé avec succès !';
          setTimeout(() => {
            this.router.navigate(['/login']);
          }, 2000);
        } else {
          this.error = response.message || 'Erreur lors de la création du compte';
        }
      },
      error: (err: any) => { // ✅ Ajoute le type 'any'
        this.loading = false;
        this.error = 'Erreur lors de la création du compte';
        
        if (err.error && err.error.errors) {
          this.error = Object.values(err.error.errors).join(', ');
        } else if (err.error && err.error.message) {
          this.error = err.error.message;
        }
        
        console.error('Register error:', err);
      }
    });
  }
}