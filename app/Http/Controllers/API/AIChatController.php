<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AIChatController extends Controller
{
    public function askQuestion(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'conversation_history' => 'sometimes|array'
        ]);

        try {
            $userMessage = $request->input('message');
            $conversationHistory = $request->input('conversation_history', []);

            // Essayez d'abord OpenAI, puis fallback aux réponses prédéfinies
            try {
                $aiResponse = $this->generateAIResponse($userMessage, $conversationHistory);
            } catch (Exception $openAiError) {
                Log::warning('OpenAI error, using fallback: ' . $openAiError->getMessage());
                $aiResponse = $this->getPredefinedResponse($userMessage);
            }

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur Assistant IA: ' . $e->getMessage());
            
            // Fallback aux réponses prédéfinies en cas d'erreur générale
            $fallbackResponse = $this->getPredefinedResponse($request->input('message'));
            
            return response()->json([
                'success' => true, // Toujours success pour ne pas casser le frontend
                'response' => $fallbackResponse,
                'timestamp' => now()
            ]);
        }
    }

    private function generateAIResponse($userMessage, $conversationHistory)
    {
        // Vérifier si la clé API est configurée
        $apiKey = config('services.openai.api_key');
        if (!$apiKey || $apiKey === 'your_openai_api_key_here') {
            throw new Exception('OpenAI API key not configured');
        }

        $systemPrompt = "Tu es un assistant médical virtuel pour une plateforme de rendez-vous médicaux. 
        Tu dois : 
        1. Répondre aux questions générales sur la santé
        2. Aider à naviguer sur la plateforme
        3. Donner des conseils généraux (pas de diagnostics)
        4. Diriger vers un médecin pour les problèmes spécifiques
        5. Ne jamais donner de diagnostics ou prescriptions

        Réponds en français, sois empathique et professionnel.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$conversationHistory,
            ['role' => 'user', 'content' => $userMessage]
        ];

        // Utilisation de HTTP client au lieu du package OpenAI pour plus de simplicité
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => config('services.openai.model', 'gpt-3.5-turbo'),
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7
        ]);

        if ($response->failed()) {
            throw new Exception('OpenAI API error: ' . $response->body());
        }

        return $response->json()['choices'][0]['message']['content'];
    }

    private function getPredefinedResponse($userMessage)
{
    $message = strtolower(trim($userMessage));

    // Réponses simples sans interpolation complexe
    $responses = [
        'bonjour' => 'Bonjour ! 👋 Je suis votre assistant médical virtuel. Comment puis-je vous aider aujourd\'hui ?',
        'salut' => 'Salut ! 😊 Je suis là pour vous aider avec vos questions de santé.',
        'hello' => 'Hello ! Comment puis-je vous assister aujourd\'hui ?',
        'rdv' => 'Pour prendre un rendez-vous, allez dans la section "Prendre RDV" et choisissez un médecin selon votre besoin.',
        'rendez' => 'Vous pouvez prendre rendez-vous en naviguant vers "Mes rendez-vous" → "Prendre un RDV".',
        'médecin' => 'Nous avons des médecins de diverses spécialités. Utilisez la fonction de recherche pour trouver le professionnel qui correspond à vos besoins !',
        'docteur' => 'Nos docteurs sont disponibles pour vous consulter. Cherchez par spécialité pour trouver le vôtre.',
        'urgence' => '🚨 En cas d\'urgence médicale, appelez immédiatement le 15 (SAMU) ou le 112 (numéro d\'urgence européen).',
        'merci' => 'Je vous en prie ! 😊 N\'hésitez pas si vous avez d\'autres questions.',
        'contact' => '📞 Vous pouvez nous contacter par email à support@medicalapp.com ou par téléphone au 01 23 45 67 89.',
        'heure' => '🕒 Nous sommes ouverts du lundi au vendredi de 8h à 20h, et le samedi de 9h à 17h.',
        'spécialité' => '🏥 Nous proposons les spécialités suivantes : généraliste, cardiologie, dermatologie, pédiatrie, gynécologie, et bien d\'autres.',
        'prix' => '💶 Les prix des consultations varient selon les médecins et spécialités. Vous pouvez voir les tarifs lors de la prise de rendez-vous.',
        'paiement' => '💳 Nous acceptons les paiements en ligne par carte bancaire ainsi que le paiement au cabinet.',
        'annuler' => '❌ Pour annuler un rendez-vous, allez dans "Mes rendez-vous", cliquez sur le rendez-vous et sélectionnez "Annuler".',
        'symptôme' => '🤒 Je ne peux pas faire de diagnostic. Si vous avez des symptômes inquiétants, veuillez consulter un médecin rapidement.',
        'diagnostic' => '⚠️ En tant qu\'assistant virtuel, je ne peux pas établir de diagnostic. Consultez un professionnel de santé pour une évaluation médicale.',
        'vaccin' => '💉 Pour toute question sur les vaccins, consultez votre médecin traitant ou un centre de vaccination.',
        'covid' => '🦠 Pour les questions COVID-19, consultez le site officiel du gouvernement ou appelez le 0 800 130 000.',
        'mental' => '🧠 Pour votre santé mentale, nous avons des psychologues et psychiatres disponibles. N\'hésitez pas à chercher dans ces spécialités.'
    ];

    foreach ($responses as $keyword => $response) {
        if (str_contains($message, $keyword)) {
            return $response;
        }
    }

    return "Je suis désolé, je ne peux répondre qu'aux questions générales sur la santé et la navigation sur notre plateforme. Pour des problèmes médicaux spécifiques, veuillez consulter un médecin. 😊";
}
}