import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { AIChatService, ChatMessage } from '../../services/ai-chat.service';
import { ToastrService } from 'ngx-toastr';
import { DatePipe } from '@angular/common';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-ai-chat',
  standalone: true, // ← S'assurer que c'est true
  imports: [CommonModule, FormsModule, DatePipe],
  templateUrl: './ai-chat.component.html',
  styleUrls: ['./ai-chat.component.css']
})
export class AIChatComponent implements OnInit {
  @ViewChild('chatContainer') private chatContainer!: ElementRef;

  isChatOpen = false;
  messages: ChatMessage[] = [];
  userInput = '';
  isLoading = false;
  useAIBackend = true; // Basculer entre AI et réponses prédéfinies

  constructor(
    private aiChatService: AIChatService,
    private toastr: ToastrService
  ) {}
  ngOnInit() {
    this.addWelcomeMessage();
  }

  private addWelcomeMessage() {
    this.messages.push({
      role: 'assistant',
      content: 'Bonjour ! 👋 Je suis votre assistant médical virtuel. Je peux vous aider pour :\n• La navigation sur la plateforme\n• Les questions générales sur la santé\n• La prise de rendez-vous\n• Les informations pratiques\n\nComment puis-je vous aider aujourd\'hui ?',
      timestamp: new Date()
    });
  }

  toggleChat() {
    this.isChatOpen = !this.isChatOpen;
    if (this.isChatOpen) {
      setTimeout(() => this.scrollToBottom(), 100);
    }
  }

  sendMessage() {
    if (!this.userInput.trim() || this.isLoading) return;

    const userMessage: ChatMessage = {
      role: 'user',
      content: this.userInput.trim(),
      timestamp: new Date()
    };

    this.messages.push(userMessage);
    this.userInput = '';
    this.isLoading = true;

    if (this.useAIBackend) {
      this.sendToAI(userMessage.content);
    } else {
      this.usePredefinedResponse(userMessage.content);
    }

    this.scrollToBottom();
  }

  private sendToAI(message: string) {
    this.aiChatService.askQuestion(message, this.messages).subscribe({
      next: (response) => {
        this.addAssistantResponse(response.response);
      },
      error: (error) => {
        console.error('Erreur API AI:', error);
        this.toastr.warning('Utilisation des réponses prédéfinies');
        this.useAIBackend = false;
        this.usePredefinedResponse(message);
      }
    });
  }

  private usePredefinedResponse(message: string) {
    const response = this.aiChatService.getPredefinedResponse(message);
    this.addAssistantResponse(response);
  }

  private addAssistantResponse(response: string) {
    const aiMessage: ChatMessage = {
      role: 'assistant',
      content: response,
      timestamp: new Date()
    };

    this.messages.push(aiMessage);
    this.isLoading = false;
    this.scrollToBottom();
  }

  clearChat() {
    this.messages = [];
    this.addWelcomeMessage();
  }

  private scrollToBottom(): void {
    setTimeout(() => {
      if (this.chatContainer) {
        this.chatContainer.nativeElement.scrollTop = 
          this.chatContainer.nativeElement.scrollHeight;
      }
    }, 100);
  }
  // Ajoutez cette méthode à votre composant AI Chat existant
public toggleChatFromHeader(): void {
  this.toggleChat();
  // Scroll to bottom quand ouvert depuis le header
  if (this.isChatOpen) {
    setTimeout(() => this.scrollToBottom(), 100);
  }
}
}