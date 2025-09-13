import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';


export interface ChatMessage {
  role: 'user' | 'assistant';
  content: string;
  timestamp: Date;
}

@Injectable({
  providedIn: 'root'
})
export class AIChatService {
  private apiUrl = 'http://localhost:8000/api/ai-chat';

  constructor(private http: HttpClient) { }

  askQuestion(message: string, history: ChatMessage[] = []): Observable<any> {
    const conversationHistory = history.map(msg => ({
      role: msg.role,
      content: msg.content
    }));

    return this.http.post(`${this.apiUrl}/ask`, {
      message,
      conversation_history: conversationHistory
    }, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      }
    });
  }

  // Méthode de fallback pour les réponses prédéfinies
  getPredefinedResponse(message: string): string {
    const lowerMessage = message.toLowerCase().trim();

    const responses: { [key: string]: string } = {
      'bonjour': 'Bonjour ! Comment puis-je vous aider aujourd\'hui ?',
      'salut': 'Salut ! Je suis là pour vous aider avec vos questions de santé.',
      'hello': 'Hello ! Comment puis-je vous assister ?',
      'rdv': 'Pour prendre un rendez-vous, allez dans la section "Prendre RDV" et choisissez un médecin selon votre besoin.',
      'rendez-vous': 'Vous pouvez prendre rendez-vous en naviguant vers "Mes rendez-vous" → "Prendre un RDV".',
      'médecin': 'Nous avons des médecins de diverses spécialités. Utilisez la fonction de recherche pour trouver le professionnel qui correspond à vos besoins !',
      'docteur': 'Nos docteurs sont disponibles pour vous consulter. Cherchez par spécialité pour trouver le vôtre.',
      'urgence': 'En cas d\'urgence médicale, appelez immédiatement le 15 (SAMU) ou le 112 (numéro d\'urgence européen).',
      'merci': 'Je vous en prie ! N\'hésitez pas si vous avez d\'autres questions.',
      'contact': 'Vous pouvez nous contacter par email à support@medicalapp.com ou par téléphone au 01 23 45 67 89.',
      'heure': `Nous sommes ouverts du lundi au vendredi de 8h à 20h, et le samedi de 9h à 17h. Il est actuellement ${new Date().toLocaleTimeString('fr-FR')}.`,
      'spécialité': 'Nous proposons les spécialités suivantes : généraliste, cardiologie, dermatologie, pédiatrie, gynécologie, et bien d\'autres.',
      'prix': 'Les prix des consultations varient selon les médecins et spécialités. Vous pouvez voir les tarifs lors de la prise de rendez-vous.',
      'paiement': 'Nous acceptons les paiements en ligne par carte bancaire ainsi que le paiement au cabinet.',
      'annuler': 'Pour annuler un rendez-vous, allez dans "Mes rendez-vous", cliquez sur le rendez-vous et sélectionnez "Annuler".',
      'résultat': 'Pour obtenir vos résultats médicaux, veuillez contacter directement le cabinet de votre médecin.',
      'ordonnance': 'Pour toute demande d\'ordonnance, veuillez prendre rendez-vous avec votre médecin.',
      'symptôme': 'Je ne peux pas faire de diagnostic. Si vous avez des symptômes inquiétants, veuillez consulter un médecin rapidement.',
      'diagnostic': 'En tant qu\'assistant virtuel, je ne peux pas établir de diagnostic. Consultez un professionnel de santé pour une évaluation médicale.',
      'vaccin': 'Pour toute question sur les vaccins, consultez votre médecin traitant ou un centre de vaccination.',
      'covid': 'Pour les questions COVID-19, consultez le site officiel du gouvernement ou appelez le 0 800 130 000.',
      'mental': 'Pour votre santé mentale, nous avons des psychologues et psychiatres disponibles. N\'hésitez pas à chercher dans ces spécialités.'
    };

    // Recherche par mot-clé
    for (const [keyword, response] of Object.entries(responses)) {
      if (lowerMessage.includes(keyword)) {
        return response;
      }
    }

    return 'Je suis désolé, je ne peux répondre qu\'aux questions générales sur la santé et la navigation sur notre plateforme. Pour des problèmes médicaux spécifiques, veuillez consulter un médecin.';
  }
}