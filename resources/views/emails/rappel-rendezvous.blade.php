<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rappel de Rendez-vous</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #FF9800; color: white; padding: 20px; text-align: center; }
        .content { background: #fff3e0; padding: 20px; border-radius: 0 0 5px 5px; }
        .details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        .button { background: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .urgent { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Rappel de Rendez-vous</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $rendezVous->patient->user->name }}</strong>,</p>
            <p class="urgent">Vous avez un rendez-vous médical <strong>DEMAIN</strong> !</p>
            
            <div class="details">
                <h3>📋 Détails du Rendez-vous</h3>
                <p><strong>Médecin :</strong> Dr. {{ $rendezVous->medecin->user->name }}</p>
                <p><strong>Spécialité :</strong> {{ $rendezVous->medecin->specialite->name }}</p>
                <p><strong>Date :</strong> {{ $rendezVous->date->format('d/m/Y') }} (Demain)</p>
                <p><strong>Heure :</strong> {{ substr($rendezVous->heure_debut, 0, 5) }}</p>
                <p><strong>Lieu :</strong> Cabinet Médical</p>
            </div>

            <p>📝 <strong>Préparez-vous :</strong></p>
            <ul>
                <li>Votre carte vitale et pièce d'identité</li>
                <li>Vos ordonnances récentes</li>
                <li>Les résultats d'examens précédents</li>
            </ul>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/rendez-vous') }}" class="button">Voir Mes Rendez-vous</a>
            </div>

            <p>❌ <em>En cas d'empêchement, merci d'annuler au plus vite.</em></p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Plateforme Médicale. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>