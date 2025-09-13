import { Routes } from '@angular/router';
import { SpecialiteListComponent } from './components/specialite-list/specialite-list.component';
import { MedecinListComponent } from './components/medecin-list/medecin-list.component';
import { PrendreRendezVousComponent } from './components/prendre-rendezvous/prendre-rendezvous.component';
import { LoginComponent } from './components/login/login.component';
import { RegisterComponent } from './components/register/register.component';
import { authGuard } from './guards/auth.guard';
import { roleGuard } from './guards/role.guard';
import { ProfileComponent } from './components/profile/profile.component';
import { MesRendezVousComponent } from './components/mes-rendezvous/mes-rendezvous.component';
import { AdminDashboardComponent } from './components/admin-dashboard/admin-dashboard.component';
import { AdminUsersComponent } from './components/admin-users/admin-users.component';
import { PaymentComponent } from './components/payment/payment.component';
import { AccueilComponent } from './components/accueil/accueil.component';
import { AdminSpecialitesComponent } from './components/admin-specialites/admin-specialites.component';
import { AdminStatistiquesComponent } from './components/admin-statistiques/admin-statistiques.component';
import { AdminPaiementsComponent } from './components/admin-paiements/admin-paiements.component';
import { AdminJustificatifsComponent } from './components/admin-justificatifs/admin-justificatifs.component';
import { PlagesHorairesComponent } from './components/plages-horaires/plages-horaires.component';
import { RdvAConfirmerComponent } from './components/rdv-a-confirmer/rdv-a-confirmer.component';
import { AgendaComponent } from './components/agenda/agenda.component';

export const routes: Routes = [
  { path: '', component: AccueilComponent }, // Page d'accueil principale
  { path: 'specialites', component: SpecialiteListComponent },
  { path: 'medecins', component: MedecinListComponent },
  { path: 'paiement', component: PaymentComponent },
  { 
    path: 'prendre-rendezvous', 
    component: PrendreRendezVousComponent,
    canActivate: [authGuard]
  },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { 
    path: 'profile', 
    component: ProfileComponent,
    canActivate: [authGuard]
  },
  { 
    path: 'mes-rendezvous', 
    component: MesRendezVousComponent,
    canActivate: [authGuard]
  },
  {
    path: 'admin',
    component: AdminDashboardComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['admin'] }
  },
  {
    path: 'admin/users',
    component: AdminUsersComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['admin'] }
  },
  {
    path: 'admin/specialites',
    component: AdminSpecialitesComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['admin'] }
  },
  {
    path: 'admin/statistiques',
    component: AdminStatistiquesComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['admin'] }
  },
  {
    path: 'admin/paiements',
    component: AdminPaiementsComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['admin'] }
  },
   { 
    path: 'admin/justificatifs', 
    component: AdminJustificatifsComponent ,
    canActivate: [authGuard, roleGuard],
    data: { roles: ['admin'] }
  },
   {
    path: 'medecin/plages-horaires',
    component: PlagesHorairesComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: 'medecin' }
  },
  {
    path: 'medecin/rdv-a-confirmer',
    component: RdvAConfirmerComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: 'medecin' }
  },
  {
    path: 'medecin/agenda',
    component: AgendaComponent,
    canActivate: [authGuard, roleGuard],
    data: { roles: 'medecin' }
  },
  { path: '**', redirectTo: '' } // Redirection pour les routes inconnues
];