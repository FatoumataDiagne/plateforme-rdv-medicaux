import { Component } from '@angular/core';
import { RouterOutlet, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService } from './services/auth.service';
import { AIChatComponent } from './components/ai-chat/ai-chat.component';
import { HeaderComponent } from './components/header/header.component';
import { FooterComponent } from './components/footer/footer.component';
import { BodyClassService } from './services/body-class.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, RouterLink, CommonModule,AIChatComponent,HeaderComponent, FooterComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'Plateforme Médicale';

  constructor(private authService: AuthService) {}

  isLoggedIn(): boolean {
    return this.authService.isLoggedIn();
  }

  get currentUser(): any {
    return this.authService.getCurrentUser();
  }

  logout(): void {
    this.authService.logout().subscribe({
      next: () => {
        window.location.reload();
      },
      error: (err) => {
        console.error('Logout error:', err);
        window.location.reload();
      }
    });
  }
}