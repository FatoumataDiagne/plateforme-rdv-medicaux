import { bootstrapApplication } from '@angular/platform-browser';
import { AppComponent } from './app/app.component';
import { appConfig } from './app/app.config'; // ← Importez appConfig

bootstrapApplication(AppComponent, appConfig) // ← Utilisez appConfig ici
  .catch(err => console.error(err));